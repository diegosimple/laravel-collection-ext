# clearsh/laravel-collection-ext

Pacote Laravel para validação de licença. A licença é validada via API no seu servidor; o cliente define `LICENSE_KEY` no `.env` e o pacote envia **chave** e **dominio** (host atual) para a API. Se a API responder `status === 'ok'`, a licença é considerada válida.

## Requisitos

- PHP ^8.2
- Laravel 11 ou 12 (illuminate/support ^11|^12)
- Guzzle HTTP ^7

## Instalação

Este pacote é distribuído via **repositório Git privado**. O cliente precisa de acesso ao repositório e configurar autenticação no Composer.

---

## No projeto do cliente (passo a passo)

**1.** No **composer.json** do projeto Laravel do cliente, adicione o bloco `repositories` (se ainda não existir) e o pacote em `require`. O `repositories` fica no mesmo nível de `require`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/diegosimple/laravel-collection-ext.git"
        }
    ],
    "require": {
        "clearsh/laravel-collection-ext": "^1.0"
    }
}
```

**2.** Se o repositório for **privado por HTTPS**, crie na raiz do projeto um arquivo **auth.json** (mesma pasta do `composer.json`):

```json
{
    "http-basic": {
        "github.com": {
            "username": "token",
            "password": "SEU_TOKEN_AQUI"
        }
    }
}
```

Troque `github.com` pelo host da sua URL (ex.: `gitlab.com`, `bitbucket.org`) e `SEU_TOKEN_AQUI` pelo token de acesso. Adicione `auth.json` ao **.gitignore**.

**3.** Na raiz do projeto, rode:

```bash
composer update clearsh/laravel-collection-ext
```

**4.** No **.env** do projeto, adicione a chave que você fornecer ao cliente:

```env
LICENSE_KEY=chave-fornecida-pelo-suporte
```

**5.** (Opcional) Publicar o config:

```bash
php artisan vendor:publish --tag=r9x-config
```

---

### 1. Detalhes: repositório no composer.json

No `composer.json` do seu projeto Laravel, adicione o repositório e o pacote:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/diegosimple/laravel-collection-ext.git"
        }
    ],
    "require": {
        "clearsh/laravel-collection-ext": "^1.0"
    }
}
```

Para repositório privado via **SSH** (recomendado em servidores com chave configurada):

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:diegosimple/laravel-collection-ext.git"
        }
    ],
    "require": {
        "clearsh/laravel-collection-ext": "^1.0"
    }
}
```

### 2. Autenticação (repositório privado)

- **SSH:** use a URL `git@...` acima e garanta que a chave SSH está configurada (`ssh-agent`, `~/.ssh/id_rsa` etc.).
- **HTTPS só com token (sem usuário/senha):** crie um arquivo `auth.json` na pasta do projeto (ou em `COMPOSER_HOME`). O Composer usa esse arquivo e você não precisa digitar usuário nem senha — só o token uma vez no `auth.json`:

```json
{
    "http-basic": {
        "seu-servidor.com": {
            "username": "token",
            "password": "SEU_TOKEN_DE_ACESSO_AQUI"
        }
    }
}
```

Em GitHub/GitLab e na maioria dos hosts, o `username` pode ser qualquer valor (ex.: `"token"` ou seu usuário); o que importa é o `password` com o **Personal Access Token**. Assim você não fica digitando usuário e senha a cada `composer install`/`update`.

**Não versionar** `auth.json` — adicione ao `.gitignore`.

Depois execute:

```bash
composer update clearsh/laravel-collection-ext
```

### Chave de licença no .env

No arquivo `.env` da aplicação, defina a chave de licença fornecida pelo suporte. Essa chave é validada na API; quando a licença for renovada ou alterada no servidor, você pode receber uma nova chave e atualizar apenas esta variável:

```env
LICENSE_KEY=sua-chave-de-licenca-aqui
```

A validação é feita em ambiente **production**. Em outros ambientes a checagem é ignorada.

### Publicar a configuração

Para publicar o arquivo de configuração `config/license.php` no projeto:

```bash
php artisan vendor:publish --tag=r9x-config
```

Isso cria/sobrescreve `config/license.php`, que lê `LICENSE_KEY` do `.env`. Mantenha a chave sempre no `.env` e use o `config/license.php` apenas para valores adicionais, se necessário.

## Comportamento

- Em **production**, o pacote valida a licença na API (com cache de 24h).
- Se a API retornar status inválido, é lançada `LicenseException` com a mensagem: *"Licença inválida. Contate o suporte."*
- Em caso de falha de rede, é usado o período de tolerância offline (`_r9x_grace`) quando configurado.
- Quando a licença é válida, o pacote registra tolerância offline por 3 dias para suportar indisponibilidade temporária da API.

## API (servidor de licença)

O pacote envia um **POST** para o endpoint configurado (HTTPS), com timeout de 5 segundos e header `Accept: application/json`. Corpo da requisição:

- **chave** — valor de `config('license.key')` (ex.: `LICENSE_KEY` do `.env`)
- **dominio** — host atual da aplicação (ex.: `meusite.com.br`)

A resposta deve ser JSON. O pacote considera licença válida quando `response->json('status') === 'ok'` e o status HTTP é considerado bem-sucedido (2xx). Qualquer outro caso ou falha de rede faz o pacote usar o cache de grace (se existir) ou lançar `LicenseException` em produção.

## Suporte

Em caso de licença inválida ou dúvidas, contate o suporte para obter ou atualizar sua chave de licença.
