<?php
/**
* Page_Model class
*/
class Page_Model
{
    private $_con;

    public function __construct()
    {
        // let's connect to our db
        $this->_connect();
    }

    public function get_page($name)
    {
        if ($name)
        {
            $query = $this->_con->query('SELECT * FROM ' . MYSQL_TABLE . ' WHERE name = "' . $this->_con->real_escape_string($name) . '" LIMIT 1');
        }
        else
        {
            // if there's no name, we get the homepage
            $query = $this->_con->query('SELECT * FROM ' . MYSQL_TABLE . ' WHERE is_home = 1 LIMIT 1');
        }

        if ($query->num_rows === 1)
        {
            return $query->fetch_object();
        }
    }

    public function get_pages()
    {
        $query = $this->_con->query('SELECT `id`, `g_id`, `title`, `name`, `child_of`, `is_home`, `is_folder` FROM ' . MYSQL_TABLE);

        if ($query->num_rows > 0)
        {
            $result = array();

            while ($row = $query->fetch_object())
            {
                array_push($result, $row);
            }

            return $result;
        }
    }

    public function rebuild($files)
    {
        // truncate our table
        $this->_delete_all_pages();

        // begin building our sql
        $sql = 'INSERT INTO ' . MYSQL_TABLE . ' (`id`, `g_id`, `title`, `name`, `body`, `child_of`, `is_home`, `is_folder`, `last_update`) VALUES ';

        // declare our sql array
        $sql_array = array();

        // build it
        $this->_build_sql_inserts($files, $sql_array);

        // merge with our sql
        $sql .= implode(', ', $sql_array);

        // execute its ass
        $query = $this->_con->query($sql);

        if ($query !== true)
        {
            return $this->_con->error;
        }

        return $query;
    }

    private function _build_sql_inserts($files, &$sql, $parent_g_id=null)
    {
        foreach ($files as $file)
        {
            // are we dealing with a standard file or folder?
            if (property_exists($file, 'children'))
            {
                // a folder
                array_push($sql, '(NULL, "' . $file->g_id . '", "' . $file->title . '", "' . $file->name . '", NULL, "' . $parent_g_id . '", 0, 1, "' . $file->last_update . '")');

                // now iterate through the children
                $this->_build_sql_inserts($file->children, $sql, $file->g_id);
            }
            else
            {
                // a standard file
                array_push($sql, '(NULL, "' . $file->g_id . '", "' . $file->title . '", "' . $file->name . '", "' . $this->_con->real_escape_string($file->body) . '", "' . $parent_g_id . '", "' . $file->is_home . '", 0, "' . $file->last_update . '")');
            }
        }
    }

    private function _connect()
    {
        // we're gonna use mysqli
        $this->_con = new mysqli(MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DB); // as defined in our config

        // if you're using PHP <5.2.9 then uncomment this line and comment out the line after
        // if (mysqli_connect_error())
        if ($this->_con->connect_error)
        {
            die('Failed to connect to DB');
        }
    }

    private function _delete_all_pages()
    {
        // delete our pages so we can start again
        $this->_con->query('TRUNCATE ' . MYSQL_TABLE);
    }
}