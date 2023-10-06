// const BASE_URL = 'https://webauthn.local:8080/api';
const BASE_URL = 'http://localhost:8080'.replace(/\/$/, '');

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
});

export const signin = (username, setError, handleResponse, handleError) => {
    if (!username.length) {
        setError({error: 'Por favor, preencha seu nome de usuário.'});
        return;
    }
    setError(null);
    postRequest('/login/init', {
        username,
    })
        .then((response) => response.json())
        .then((response) => {
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
            requestOptions.allowCredentials.map((credential) => {
                credential.id = parseBase64url(credential.id);
                return credential;
            });
            requestOptions.timeout = 30000;

            console.log('CredentialRequestOptions', requestOptions);

            navigator.credentials.get({
                publicKey: requestOptions,
            }).then((credential) => {
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
                postRequest('/login/assertion', assertion)
                    .then((response) => response.json())
                    .then(handleResponse)
                    .catch(handleError);
            })
                .catch(handleError);
        })
        .catch(handleError);
};

export const signup = (username, setError, handleResponse, handleError) => {
    if (!username.length) {
        setError({error: 'Por favor, preencha seu nome de usuário.'});
        return;
    }
    setError(null);
    fetch(`${BASE_URL}/register/init`, {
        method: 'POST',
        credentials: 'include',
        body: JSON.stringify({
            username,
        }),
        headers: {
            'Content-Type': 'application/json',
        },
    })
        .then((response) => response.json())
        .then((response) => {
            if ((response.statusCode !== 200) || (!response.data)) {
                return handleError(response);
            }

            const createOptions = {...response.data};
            createOptions.challenge = toBuffer(createOptions.challenge);
            createOptions.user.id = toBuffer(createOptions.user.id);

            console.log('CredentialCreateOptions', createOptions);

            navigator.credentials.create({
                publicKey: createOptions,
            }).then((credential) => {
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
                fetch(`${BASE_URL}/register/attestation`, {
                    method: 'POST',
                    body: JSON.stringify(registration),
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                })
                    .then((response) => response.json())
                    .then(handleResponse)
                    .catch((err) => {
                        handleError(err);
                    });
            })
                .catch((err) => {
                    handleError(err);
                });
        })
        .catch((err) => {
            handleError(err);
        });
};
