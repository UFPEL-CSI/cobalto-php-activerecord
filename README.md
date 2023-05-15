# Cobalto ActiveRecord - Version 2.0.0-rc #

## Introdução ##

Um breve resumo do que é ActiveRecord:

> Registro ativo é uma abordagem para acessar dados em um banco de dados. Uma tabela ou visualização do banco de dados é agrupada em uma classe,
> assim, uma instância de objeto está vinculada a uma única linha na tabela. Após a criação de um objeto, uma nova linha é adicionada a
> a tabela ao salvar. Qualquer objeto carregado obtém suas informações do banco de dados; quando um objeto é atualizado, o
> linha correspondente na tabela também é atualizada. A classe wrapper implementa métodos de acesso ou propriedades para
> cada coluna na tabela ou visualização.

Mais detalhes podem ser encontrados [aqui](http://en.wikipedia.org/wiki/Active_record_pattern).

Essa implementação é inspirada e, portanto, empresta muito do ActiveRecord do Ruby on Rails.
Tentamos manter suas convenções enquanto nos desviamos principalmente por conveniência ou necessidade.
Claro, existem algumas diferenças que serão óbvias para o usuário se ele estiver familiarizado com trilhos.

### Minimum Requirements ###

- PHP = 7.0.0
* Driver PDO para seu respectivo banco de dados com personalizações feitas pela equipe de desenvolvimento da ufpel

### Supported Databases ###

- MySQL
- PostgreSQL
### Features ###

- Finder methods
- Dynamic finder methods
- Writer methods
- Relationships
- Validations
- Callbacks
- Serializations (json/xml)
- Transactions
- Support for multiple adapters
- Miscellaneous options such as: aliased/protected/accessible attributes

## Instalação ##

```php
composer require ufpel-csi/cobalto-php-activerecord
```

### Configuração ###

A configuração é muito fácil e direta. Existem essencialmente apenas três pontos de configuração com os quais você deve se preocupar:

1. Configurando o diretório auto_load do modelo.
2. Configurando suas conexões de banco de dados.
3. Configurando a conexão do banco de dados a ser usada em seu ambiente.

Exemplo:

```php
ActiveRecord\Config::initialize(function($cfg)
{
   $cfg->set_model_directory('/path/to/your/model_directory');
   $cfg->set_connections(
     array(
       'development' => 'mysql://username:password@localhost/development_database_name',
       'test' => 'mysql://username:password@localhost/test_database_name',
       'production' => 'mysql://username:password@localhost/production_database_name'
     )
   );
});
```

O PHP ActiveRecord será o padrão para usar seu banco de dados de desenvolvimento. Para teste ou produção, basta definir o padrão
conexão de acordo com seu ambiente atual ('teste' ou 'produção'):

```php
ActiveRecord\Config::initialize(function($cfg)
{
  $cfg->set_default_connection(your_environment);
});
```

Depois de definir essas três configurações, você estará pronto. ActiveRecord cuida do resto para você.
Ele não requer que você mapeie seu esquema de tabela para arquivos yaml/xml. Ele consultará o banco de dados para obter essas informações e
armazene-o em cache para que não faça várias chamadas ao banco de dados para um único esquema.

## Testes ##
```php
composer update
vendor/bin/phpunit -c phpunit.xml test/
```

## Exemplos ##

### Retrieve ###
Estes são seus métodos básicos para encontrar e recuperar registros de seu banco de dados.
Veja a seção *Finders* para mais detalhes.

```php
$post = Post::find(1);
echo $post->title; # 'My first blog post!!'
echo $post->author_id; # 5

# also the same since it is the first record in the db
$post = Post::first();

# finding using dynamic finders
$post = Post::find_by_name('The Decider');
$post = Post::find_by_name_and_id('The Bridge Builder',100);
$post = Post::find_by_name_or_id('The Bridge Builder',100);

# finding using a conditions array
$posts = Post::find('all',array('conditions' => array('name=? or id > ?','The Bridge Builder',100)));
```

### Create ###
Aqui criamos uma nova postagem instanciando um novo objeto e então invocando o método save().

```php
$post = new Post();
$post->title = 'My first blog post!!';
$post->author_id = 5;
$post->save();
# INSERT INTO `posts` (title,author_id) VALUES('My first blog post!!', 5)
```

### Update ###
Para atualizar, você só precisa encontrar um registro primeiro e depois alterar um de seus atributos.
Ele mantém um array de atributos que estão "sujos" (que foram modificados) e assim nosso
sql só atualizará os campos modificados.

```php
$post = Post::find(1);
echo $post->title; # 'My first blog post!!'
$post->title = 'Some real title';
$post->save();
# UPDATE `posts` SET title='Some real title' WHERE id=1

$post->title = 'New real title';
$post->author_id = 1;
$post->save();
# UPDATE `posts` SET title='New real title', author_id=1 WHERE id=1
```

### Delete ###
Excluir um registro não *destruirá* o objeto. Isso significa que ele chamará sql para deletar
o registro em seu banco de dados, mas você ainda pode usar o objeto se precisar.

```php
$post = Post::find(1);
$post->delete();
# DELETE FROM `posts` WHERE id=1
echo $post->title; # 'New real title'
```

## Créditos ##

Todo o código abaixo é baseado na biblioteca php-activerecord, originalmente criada por:
kien la
Jacques Fuentes

* [@kla](https://github.com/kla) - Kien La
* [@jpfuentes2](https://github.com/jpfuentes2) - Jacques Fuentes

<http://www.phpactiverecord.org/>
