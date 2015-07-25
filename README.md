## ClearPDO

ClearPDO makes connecting with databases and running queries extremely simple.

### Usage Instructions

ClearPDO extends PDO so all the things you can do with vanilla PDO your can do with ClearPDO. For example:

```PHP
$db = new ClearPDO\PDO("mysql:host=$host;dbname=$database;charset=UTF8", $username, $password);

$db->prepare("SELECT * FROM provinces WHERE population > ?");
$db->execute(array(500000));

$provinces = $db->fetchAll();
```

But you can also use the many convenience methods that ClearPDO provides.

**Creating a MySQL Connect**

Instead of wasting time manually creating a DSN string you can use the following helper method to create a connection using a configuration array.

```PHP
$db = ClearPDO\PDO::createMysqlConnection([
	'host'      => 'localhost',
	'port'      => '3396',
	'database'  => 'blog',
	'username'  => 'root',
	'password'  => '3GwZFRjbGezY',
	'charset'   => 'utf8',
	'collation' => 'utf8_unicode_ci',
]);
```
Or alternative using a socket connection.
```PHP
$db = ClearPDO\PDO::createMysqlConnection([
	'unix_socket' => '/var/run/mysqld/mysqld.sock',
	'database'    => 'blog',
	'username'    => 'root',
	'password'    => '3GwZFRjbGezY',
	'charset'     => 'utf8',
	'collation'   => 'utf8_unicode_ci',
]);
```

**Using the Select Method**

```PHP
$statement = $db->select('SELECT content FROM posts WHERE status = :status', [':status' => 'published']);
while ($post = $statement->fetch()) {
	print $post->content;
}
```

**Using the Results Method**

```PHP
$posts = $db->results('SELECT content FROM posts WHERE status = :status', [':status' => 'published']);
foreach ($posts as $post) {
	print $post->content;
}
```

**Using the Lists Method**

```PHP
$posts = $db->lists('SELECT id, content FROM posts WHERE status = :status', [':status' => 'published']);
foreach ($posts as $id => $content) {
	print $content;
}
```

**Using the Result Method**

```PHP
$post = $db->result('SELECT content FROM posts WHERE id = :id', [':id' => 4]);
print $post->content;
```

**Using the Column Method**

```PHP
$content = $db->column('SELECT content FROM posts WHERE id = :id', [':id' => 4]);
print $content;
```

**Using the Insert Method**

```PHP
$db->insert('posts', [
	'status'  => 'draft'
	'content' => 'Vestibulum dictum, nunc vel pulvinar.',
	'created' => new DateTime,
]);
```

**Using the Update Method**

```PHP
$data = [
	'status'  => 'published',
	'updated' => new DateTime,
];

$db->insert('posts', $data, 'id = :postID', [':postID' => 4]);
```
