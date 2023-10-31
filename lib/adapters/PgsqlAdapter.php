<?php

namespace ActiveRecord;

use PDO;

/**
 * Adapter for Postgres (not completed yet).
 */
class PgsqlAdapter extends Connection
{
	public static $QUOTE_CHARACTER = '"';
	public static $DEFAULT_PORT = 5432;

	public function supports_sequences()
	{
		return true;
	}

	public function get_sequence_name($table, $column_name)
	{
		return "{$table}_{$column_name}_seq";
	}

	public function next_sequence_value($sequence_name)
	{
		return "nextval('" . str_replace("'", "\\'", $sequence_name) . "')";
	}

	public function limit($sql, $offset, $limit)
	{
		return $sql . ' LIMIT ' . intval($limit) . ' OFFSET ' . intval($offset);
	}

	public function query_column_info($table)
	{
		$full_table = explode('.', $table);
		$table = (count($full_table) > 1 ? $full_table[1] : $full_table[0]);
		$schema = (count($full_table) > 1 ? $full_table[0] : 'public');

		if ($this->getDbVersion() > 12) {
			$sql = <<<SQL
SELECT
    a.attname AS field,
    a.attlen,
    REPLACE(pg_catalog.format_type(a.atttypid, a.atttypmod), 'character varying', 'varchar') AS type,
    a.attnotnull AS not_nullable,
    (SELECT 't'
        FROM pg_index
        WHERE c.oid = pg_index.indrelid
        AND a.attnum = ANY (pg_index.indkey)
        AND pg_index.indisprimary = 't'
    ) IS NOT NULL AS pk,
    REGEXP_REPLACE(REGEXP_REPLACE(REGEXP_REPLACE((SELECT pg_get_expr(pg_attrdef.adbin, pg_attrdef.adrelid)
        FROM pg_attrdef
        WHERE c.oid = pg_attrdef.adrelid
        AND pg_attrdef.adnum=a.attnum
    ),'::[a-z_ ]+',''),'''$',''),'^''','') AS default
FROM pg_attribute as a
join pg_class as c on a.attrelid = c.oid
join pg_type t on a.atttypid = t.oid
join pg_namespace as e on c.relnamespace = e.oid
WHERE c.relname = ?
and e.nspname = ?
AND a.attnum > 0
ORDER BY a.attnum
SQL;
		} else {
			$sql = <<<SQL
SELECT
    a.attname AS field,
    a.attlen,
    REPLACE(pg_catalog.format_type(a.atttypid, a.atttypmod), 'character varying', 'varchar') AS type,
    a.attnotnull AS not_nullable,
    (SELECT 't'
        FROM pg_index
        WHERE c.oid = pg_index.indrelid
        AND a.attnum = ANY (pg_index.indkey)
        AND pg_index.indisprimary = 't'
    ) IS NOT NULL AS pk,
    REGEXP_REPLACE(REGEXP_REPLACE(REGEXP_REPLACE((SELECT pg_attrdef.adsrc
        FROM pg_attrdef
        WHERE c.oid = pg_attrdef.adrelid
        AND pg_attrdef.adnum=a.attnum
    ),'::[a-z_ ]+',''),'''$',''),'^''','') AS default
FROM pg_attribute as a
join pg_class as c on a.attrelid = c.oid
join pg_type t on a.atttypid = t.oid
join pg_namespace as e on c.relnamespace = e.oid
WHERE c.relname = ?
and e.nspname = ?
AND a.attnum > 0
ORDER BY a.attnum
SQL;
		}
		$values = [$table, $schema];
		return $this->query($sql, $values);
	}

	public function query_for_tables()
	{
		return $this->query("SELECT tablename FROM pg_tables WHERE schemaname NOT IN('information_schema','pg_catalog')");
	}

	public function create_column(&$column)
	{
		$c = new Column();
		$c->inflected_name	= Inflector::instance()->variablize($column['field']);
		$c->name			= $column['field'];
		$c->nullable		= ($column['not_nullable'] ? false : true);
		$c->pk				= ($column['pk'] ? true : false);
		$c->auto_increment	= false;

		if (substr($column['type'], 0, 9) == 'timestamp') {
			$c->raw_type = 'datetime';
			$c->length = 19;
		} elseif ($column['type'] == 'date') {
			$c->raw_type = 'date';
			$c->length = 10;
		} else {
			preg_match('/^([A-Za-z0-9_]+)(\(([0-9]+(,[0-9]+)?)\))?/', $column['type'], $matches);

			$c->raw_type = (count($matches) > 0 ? $matches[1] : $column['type']);
			$c->length = count($matches) >= 4 ? intval($matches[3]) : intval($column['attlen']);

			if ($c->length < 0) {
				$c->length = null;
			}
		}

		$c->map_raw_type();

		if ($column['default']) {
			preg_match("/^nextval\('(.*)'\)$/", $column['default'], $matches);

			if (count($matches) == 2) {
				$c->sequence = $matches[1];
			} else {
				$c->default = $c->cast($column['default'], $this);
			}
		}
		return $c;
	}

	public function set_encoding($charset)
	{
		$this->query("SET NAMES '$charset'");
	}

	public function native_database_types()
	{
		return [
			'primary_key' => 'serial primary key',
			'string' => ['name' => 'character varying', 'length' => 255],
			'text' => ['name' => 'text'],
			'integer' => ['name' => 'integer'],
			'float' => ['name' => 'float'],
			'datetime' => ['name' => 'datetime'],
			'timestamp' => ['name' => 'timestamp'],
			'time' => ['name' => 'time'],
			'date' => ['name' => 'date'],
			'binary' => ['name' => 'binary'],
			'boolean' => ['name' => 'boolean'],
		];
	}

	private function getDbVersion()
	{
		$sql = "SELECT replace(
            replace(version(), 'PostgreSQL ', ''),
            substring(replace(version(), 'PostgreSQL ', ''), position(' ' in  replace(version(), 'PostgreSQL ', '')))
            , ''
            ) as ver;";

		$sth = $this->query($sql);
		$row = $sth->fetch(PDO::FETCH_NUM);
		$version = $row[0];
		$pos = strrpos($version, '.');
		return intval(substr($version, 0, $pos));
	}

}
