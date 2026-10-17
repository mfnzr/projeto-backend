# projeto-backend

## Descrição do projeto

Sistema de vendas para uma casa de carnes assadas (frango, porco, costela), desenvolvido na disciplina de Desenvolvimento de Aplicações Back-End (TSI34D) da UTFPR Guarapuava.

O sistema controla produtos, produção diária, compras, clientes, pedidos e acompanhamentos, com acesso separado para administradores e atendentes.

Mais detalhes na [Wiki](https://github.com/mfnzr/projeto-backend/wiki).

## Como executar o projeto

Dependências: apenas **Docker** e **Docker Compose**.

```
git clone https://github.com/mfnzr/projeto-backend.git
cd projeto-backend
cp .env.example .env
./run composer install
./run up -d
./run db:reset
./run db:populate
```

Acesse [localhost](http://localhost).

Permissão da pasta de uploads, se necessário:

```
sudo chown www-data:www-data public/assets/uploads
```

### Testes e linters

```
./run test          # PHPUnit
./run test:browser  # Codeception (aceitação)
./run phpcs         # PHP_CodeSniffer
./run phpstan       # PHPStan
```

## Como contribuir

1. Crie a branch da entrega a partir da `main` com o prefixo `epic/` (ex.: `epic/autenticacao`).
2. Crie as branches de funcionalidade a partir da epic com o prefixo `feature/` (ex.: `feature/login`).
3. Abra Pull Requests das features para a epic. O CI (GitHub Actions) precisa passar.
4. Ao concluir a entrega, abra a PR da epic para a `main`.

## Licença

Este projeto está sob a licença MIT.
