# smart-gallery-filter wordpress plugin (Elementor + Pods)
Plugin de Wordpress que adiciona um widget para Elementor que permite a criação de uma galeria filtrável por taxonomias, CPTs e campos personalizados do Pods Framework. Fácil, gratuito e flexível para WordPress.

## 📋 Pré-requisitos do ambiente de desenvolvimento

- [Docker](https://www.docker.com/) instalado
- [DDEV](https://ddev.com/) instalado
- Git

## ⚡ Configuração do ambiente

1. Clone o repositório:
	```sh
	git clone https://github.com/seu-usuario/smart-gallery-filter.git
	cd smart-gallery-filter
	```

2. Inicie o ambiente DDEV:
	```sh
	ddev start
	```

3. Execute o script de configuração do WordPress:
	```sh
	ddev exec setup-wordpress
	```
	Esse script irá:
	- Baixar os arquivos do WordPress
	- Instalar o WordPress com dados padrão
	- Ativar o plugin smart-gallery-filter

4. Acesse o site:
	- [https://smart-gallery-filter.ddev.site](https://smart-gallery-filter.ddev.site)

## 🔑 Dados padrão de acesso
- Usuário: `admin`
- Senha: `admin`
- Email: `admin@local.test`

## 📝 Observações
- O plugin será ativado automaticamente após a instalação do WordPress.
- Para instalar outros plugins ou temas, utilize os comandos:
  ```sh
  ddev wp plugin install <nome-do-plugin>
  ddev wp theme install <nome-do-tema>
  ```

## 📄 Documentação oficial
- [DDEV Docs](https://ddev.readthedocs.io/en/stable/)
- [WordPress CLI](https://developer.wordpress.org/cli/commands/)
