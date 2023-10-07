import {useState} from 'preact/hooks';
import {signin, signup} from './functions.js';

export function App() {
    if (!window.PublicKeyCredential) {
        return (
            <div>WebAuthn indisponível</div>
        );
    }

    const [user, setUser] = useState(null);
    const [username, setUsername] = useState('');
    const [error, setError] = useState(null);

    const handleError = (error) => {
        console.error(error);
        setError(error);
    };

    const handleSuccess = (response) => {
        if ((response.statusCode !== 200) || (!response.data)) {
            return handleError(response);
        }

        if ((response.data.status !== true) || (!response.data.user.id)) {
            return handleError(response);
        }

        console.log('Resposta', response);
        setUser(response.data.user);
    };

    return (
        <>
            <div>
                <h1>Demonstração de WebAuthn</h1>
                <div>
                    <a href="https://viniciuscampitelli.com">Vinícius Campitelli</a>
                </div>
                {(user) ? null : (
                    <div className="form">
                        <input type="text" placeholder="Seu nome de usuário" value={username}
                               onChange={e => setUsername(e.target.value.replaceAll(/[^a-zA-Z0-9_-]+/g, '-'))}/>
                        <div className="d-flex">
                            <button type="button"
                                    onClick={() => signin(username, setError, handleSuccess, handleError)}>
                                Entrar
                            </button>
                            <button type="button"
                                    onClick={() => signup(username, setError, handleSuccess, handleError)}>
                                Cadastrar
                            </button>
                        </div>
                    </div>
                )}
            </div>
            {(user) ? (
                <div>
                    <h2>Bem-vindo, {user.displayName}</h2>
                    <pre>{JSON.stringify(user, null, 2)}</pre>
                </div>
            ) : ((error) ? (
                <div id="error">
                    <p>O seguinte erro ocorreu:</p>
                    <pre>{JSON.stringify(error, null, 2)}</pre>
                </div>
            ) : null)}
        </>
    );
}
