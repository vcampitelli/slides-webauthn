const base64url = require('base64url');

const signup = async (challengeThatShouldComeFromTheServer, user) => {
    return await navigator.credentials.create({
        publicKey: {
            challenge: base64url.toBuffer(challengeThatShouldComeFromTheServer),
            rp: {
                name: 'Palestra WebAuthn | Vinícius Campitelli',
                id: 'webauthn.viniciuscampitelli.com',
            },
            user: {
                id: base64url.toBuffer(user.id),
                name: user.name,
                displayName: user.displayName,
            },
            pubKeyCredParams: [{alg: -7, type: 'public-key'}], // -7 => ES256
            authenticatorSelection: {
                authenticatorAttachment: 'platform', // ou "cross-platform"
            },
            timeout: 60000,
            attestation: 'direct',
        },
    });
};

const LOCAL_STORAGE_ID = 'webauthn_credential_id';
const $status = document.getElementById('status');

document.getElementById('signup').addEventListener('click', () => {
    signup(
        String(Math.random()),
        {
            id: '1234',
            name: 'Vinícius Campitelli',
            displayName: 'Vinícius',
        },
    ).then((credential) => {
        $status.innerText = 'Registrado';
        console.group('Registro');
        console.log(credential);
        localStorage.setItem(LOCAL_STORAGE_ID, credential.id);
        console.groupEnd();
    }).catch((err) => {
        $status.innerText = 'Erro: ' + err.message;
        console.error(err);
        alert(err.message);
    });
});

const signin = async (challengeThatShouldComeFromTheServer, credentialIdThatShouldComeFromTheServer) => {
    const publicKeyCredentialRequestOptions = {
        challenge: base64url.toBuffer(challengeThatShouldComeFromTheServer),
        allowCredentials: [
            {
                id: base64url.toBuffer(credentialIdThatShouldComeFromTheServer),
                type: 'public-key',
                transports: ['usb', 'ble', 'nfc'],
            }],
        timeout: 60000,
    };

    return await navigator.credentials.get({
        publicKey: publicKeyCredentialRequestOptions,
    });
};

document.getElementById('signin').addEventListener('click', async () => {
    const credentialId = localStorage.getItem(LOCAL_STORAGE_ID);
    if (!credentialId) {
        alert('Você deve se cadastrar primeiro.');
        return;
    }

    console.group('Log in');
    try {
        const result = await signin(String(Math.random()), credentialId);
        console.log('Credencial recuperada');
        $status.innerText = 'Logado';
        console.log(result);
    } catch (err) {
        $status.innerText = 'Erro: ' + err.message;
        console.error(err);
        alert(err.message);
    }
    console.groupEnd();
});
