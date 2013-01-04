<?php
/**
* Page_Controller class
*/
class Page_Controller extends Base_Controller
{
    private $_page_model;

    public function __construct($matches)
    {
        // load the model
        $this->_page_model = $this->load_model('page');

        // let's route to the requested page!
        $this->_handle_route($matches);
    }

    private function _fetch_menu($select_page_id)
    {
        // fetch all the pages
        $pages = $this->_page_model->get_pages();

        // loop through the pages to create our hash for the menu
        return $this->_populate_menu($select_page_id, $pages);
    }

    private function _fetch_page($page_name=null)
    {
        // fetch the page
        return $this->_page_model->get_page($page_name);
    }

    private function _handle_route($matches)
    {
        // create a var
        $name = $matches[0];

        // check for the page, if there isn't one then render the homepage
        $page = $this->_fetch_page($name);

        // check for the page
        if (!$page)
        {
            // 404 poo
            require_once '404.php';
        }
        else
        {
            // render the page
            $this->load_view('page', array(
                'page' => $page,
                'menu' => $this->_fetch_menu($page->id)
            ));
        }
    }

    private function _populate_menu($select_page_id, $pages, $parent_id=null)
    {
        // create our array
        $menu = array();

        foreach ($pages as $page)
        {
            // ignore pages that aren't children of parent
            if ((string)$page->child_of !== (string)$parent_id)
            {
                continue;
            }

            // create our item
            $item = new GMenuItem($page);

            // is this page selected?
            $item->is_selected = $page->id === $select_page_id;

            // find some children
            $item->children = $this->_populate_menu($select_page_id, $pages, $page->g_id);

            // add to array
            array_push($menu, $item);
        }

        return $menu;
    }
}

/**
* MenuItem
*/
class GMenuItem
{
    public $title;
    public $name;
    public $children;
    public $is_home;
    public $is_selected;

    public function __construct($item)
    {
        $this->title = $item->title;
        $this->name = $item->name;
        $this->is_home = (bool)$item->is_home;
    }
}