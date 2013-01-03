<?php
/**
* Base_Controller
*/
class Base_Controller
{
    private $_path_controller = 'controller/%s_controller';
    private $_path_model = 'model/%s_model';
    private $_path_view = 'view/%s_view';

    public function __construct()
    {
    }

    protected function load_view($file, $data=null)
    {
        $this->_require(sprintf($this->_path_view, $file), $data);
    }

    private function _require($path, $data)
    {
        if ($data)
        {
            extract($data);
        }

        require_once $path . '.php';
    }
}