# WebAuthn: o "novo" padrão de autenticação na Web

Slides da palestra sobre [WebAuthn](https://www.rfc-editor.org/rfc/rfc8809).

Acesse os slides localmente clonando este repositório e acessando o arquivo [`docs/index.html`](./docs/index.html) em seu navegador.

```shell
$ git clone --recursive git@github.com:vcampitelli/slides-webauthn.git
```

Ou acesse os slides hospedados em [viniciuscampitelli.com/slides-webauthn](https://viniciuscampitelli.com/slides-webauthn).

## Demo

Para rodar a demo localmente:

1. Instale o [mkcert](https://github.com/FiloSottile/mkcert) ou use a própria [OpenSSL](https://www.openssl.org/) para criar uma CA e gerar um certificado SSL para seu site local
2. Adicione uma entrada no `/etc/hosts` para `webauthn.local`
    * Se estiver usando Windows, o caminho é `C:\WINDOWS\System32\drivers\etc\hosts`
3. Clone este repositório e  entre na pasta `demo`
4. Acesse a aplicação em [webauthn.local:8080](https://webauthn.local:8080)

```shell
$ git clone --recursive git@github.com:vcampitelli/slides-webauthn.git
$ cd demo
$ npm run mkcert
$ echo "127.0.0.1 webauthn.local" | sudo tee -a /etc/hosts
$ npm run build
$ npm run serve
```

Para executar a demo remotamente, acesse [webauthn.viniciuscampitelli.com](https://webauthn.viniciuscampitelli.com).
