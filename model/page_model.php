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

    private function get_page($name)
    {
        $query = $this->_con->query('SELECT * FROM ' . MYSQL_TABLE . ' WHERE name = "' . $this->_con->real_escape_string($name) . '" LIMIT 1');

        if ($query)
        {
            return $query->fetch_object();
        }
    }

    private function get_pages()
    {
        $query = $this->_con->query('SELECT * FROM ' . MYSQL_TABLE);

        if ($query)
        {
            return $query->fetch_all();
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
}