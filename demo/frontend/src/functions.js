const BASE_URL = import.meta.env.VITE_API_BASE_URL.replace(/\/$/, '');

const noPadding = str => str.replace(/={1,2}$/, '');

// @see @passwordless-id/webauthn
const parseBuffer = (buffer) => String.fromCharCode(...new Uint8Array(buffer));

const toBase64url = (buffer) => {
    const txt = btoa(parseBuffer(buffer)); // base64
    return txt.replaceAll('+', '-').replaceAll('/', '_');
};

const toBuffer = (txt) => {
    return Uint8Array.from(txt, c => c.charCodeAt(0)).buffer;
};

const parseBase64url = (txt) => {
    txt = txt.replaceAll('-', '+').replaceAll('_', '/'); // base64url -> base64
    return toBuffer(atob(txt));
};

const postRequest = (uri, data) => fetch(`${BASE_URL}${uri}`, {
    method: 'POST',
    credentials: 'include',
    body: JSON.stringify(data),
    headers: {
        'Content-Type': 'application/json',
    },
}).then(response => response.json());

export const signin = async (username, setError, handleSuccess, handleError) => {
    if (!username.length) {
        setError({error: 'Por favor, preencha seu nome de usuário.'});
        return;
    }
    setError(null);

    try {
        const response = await postRequest('/login/init', {
            username,
        });
        if ((response.statusCode !== 200) || (!response.data)) {
            return handleError(response);
        }

        const requestOptions = {...response.data};

        if (!requestOptions.allowCredentials) {
            return handleError({
                error: 'Nenhuma credencial foi registrada para o usuário.',
            });
        }

        requestOptions.challenge = toBuffer(requestOptions.challenge);
        requestOptions.allowCredentials.forEach((credential) => credential.id = parseBase64url(credential.id));
        requestOptions.timeout = 30000;

        console.log('CredentialRequestOptions', requestOptions);

        const credential = await navigator.credentials.get({
            publicKey: requestOptions,
        });
        console.log('Credencial retornada', credential);

        const assertion = {
            authenticatorAttachment: credential.authenticatorAttachment,
            id: credential.id,
            rawId: toBase64url(credential.rawId),
            type: credential.type,
            response: {
                authenticatorData: noPadding(toBase64url(credential.response.authenticatorData)),
                clientDataJSON: noPadding(toBase64url(credential.response.clientDataJSON)),
                signature: noPadding(toBase64url(credential.response.signature)),
            },
            user: {
                id: response.data.user.id,
            },
        };
        const user = await postRequest('/login/assertion', assertion);
        handleSuccess(user);
    } catch (err) {
        handleError(err);
    }
};

export const signup = async (username, setError, handleSuccess, handleError) => {
    if (!username.length) {
        setError({error: 'Por favor, preencha seu nome de usuário.'});
        return;
    }
    setError(null);
    try {
        const response = await postRequest('/register/init', {
            username,
        });
        if ((response.statusCode !== 200) || (!response.data)) {
            return handleError(response);
        }

        const createOptions = {...response.data};
        createOptions.challenge = toBuffer(createOptions.challenge);
        createOptions.user.id = toBuffer(createOptions.user.id);

        console.log('CredentialCreateOptions', createOptions);

        const credential = await navigator.credentials.create({
            publicKey: createOptions,
        });
        console.log('Credencial criada', credential);

        const registration = {
            id: credential.id,
            rawId: toBase64url(credential.rawId),
            type: credential.type,
            response: {
                attestationObject: noPadding(toBase64url(credential.response.attestationObject)),
                clientDataJSON: noPadding(toBase64url(credential.response.clientDataJSON)),
            },
            user: {
                id: parseBuffer(response.data.user.id),
            },
        };

        const user = await postRequest('/register/attestation', registration);
        handleSuccess(user);
    } catch (err) {
        handleError(err);
    }
};
