<?php
/**
* Admin_Controller class
*/
class Admin_Controller extends Base_Controller
{
    private $_recur_counter = array();
    private $_recur_limit = 3;

    private $_client;
    private $_client_oauth;
    private $_token;

    public function __construct($matches)
    {
        // start our admin session
        session_start();

        // set up client
        $this->_set_up_client();

        // handle route
        $this->_handle_route($matches);
    }

    private function _auth()
    {
        // check for access_token
        if (isset($_SESSION['access_token']))
        {
            $this->_client->setAccessToken($_SESSION['access_token']);
        }

        // check for user auth
        if ($access_token = $this->_client->getAccessToken())
        {
            // set local token
            $session = json_decode($access_token);

            $this->_token = $session->access_token;

            // show edit page
            return $this->_edit();
        }

        // if we've got here then the user isn't authed, so let's auth their ass
        header('location: ' . $this->_client->createAuthUrl());
    }

    private function _auth_check($code)
    {
        // auth the user
        $this->_client->authenticate($code);

        // check for access token
        if ($this->_client->getAccessToken())
        {
            // check the user's email and make sure it falls within our admin emails
            $user = $this->_client_oauth->userinfo->get();

            $user_email = filter_var($user['email'], FILTER_SANITIZE_EMAIL);

            // prepare our admin emails
            $emails = explode(' ', ADMINS);

            foreach ($emails as $email)
            {
                if ($email === $user_email)
                {
                    $this->_auth_finish();

                    break;
                }
            }
        }

        die('Auth failed');
    }

    private function _auth_finish()
    {
        // set the token in the session
        $_SESSION['access_token'] = $this->_client->getAccessToken();

        // take the user to the edit page
        header('location: ' . URL_PREFIX . 'admin/');
    }

    private function _edit()
    {
        // set some vars
        $files = null;
        $success = null;

        // check for a post request
        if (isset($_POST['folder']))
        {
            // check for home page file
            if (isset($_POST['file']))
            {
                // update the site
                $success = $this->_update($_POST['folder'], $_POST['file']);
            }
            else
            {
                // get the files so the user can select the homepage
                $files = $this->_make_req('https://www.googleapis.com/drive/v2/files?q=' . urlencode('"' . $_POST['folder'] . '" in parents and mimeType = "application/vnd.google-apps.document"'))->items;
            }
        }

        // prepare our folders array
        $folders = $this->_make_req('https://www.googleapis.com/drive/v2/files?q=' . urlencode('"root" in parents and mimeType = "application/vnd.google-apps.folder"'))->items;

        // load the view
        $this->load_view('admin', array(
            'folders' => $folders,
            'files' => $files,
            'success' => $success
        ));
    }

    private function _get_document_contents($url)
    {
        // make the req
        $html = $this->_make_req($url, false);

        // prepare our dom
        $doc = new DOMDocument();

        // load el html
        $doc->loadHTML($html);

        // get the body
        $body = $doc->getElementsByTagName('body')->item(0);

        // now get the body as html
        $body = $doc->saveXML($body);

        // strip the body tags
        $body = preg_replace('/\<\/?body([^\>]+)?\>/im', '', $body);

        // return the body
        return $body;
    }

    private function _handle_route($matches)
    {
        switch ($matches[0])
        {
            case 'callback':
                // check the user
                $this->_auth_check(substr($matches[1], 6)); // remove ?code= from the start

            break;

            default:
                // auth the user
                $this->_auth();

            break;
        }
    }

    private function _increment_recur_counter($url)
    {
        if (isset($this->_recur_counter[$url]))
        {
            $this->_recur_counter[$url]++;
        }
        else
        {
            $this->_recur_counter[$url] = 1;
        }
    }

    private function _iterate_over_files($folder_id, $home_file_id=null)
    {
        // prepare our array
        $files = array();

        // get folder contents
        $data = $this->_make_req('https://www.googleapis.com/drive/v2/files?q=' . urlencode('"' . $folder_id . '" in parents'));

        // iterate over entries
        foreach ($data->items as $item)
        {
            // check if item is a folder, if so, iterate over it
            if ($item->mimeType === 'application/vnd.google-apps.folder')
            {
                $folder = new GFolder($item);

                $folder->children = $this->_iterate_over_files($item->id, $home_file_id);

                array_push($files, $folder);
            }
            elseif ($item->mimeType === 'application/vnd.google-apps.document')
            {
                $export_links = (array)$item->exportLinks;

                $file = new GFile($item, $home_file_id);

                $file->body = $this->_get_document_contents($export_links['text/html']);

                array_push($files, $file);
            }
        }

        return $files;
    }

    private function _make_req($url, $json=true)
    {
        // prepare the request
        $req = new Google_HttpRequest($url);

        // get the io client
        $client = $this->_client;
        $io = $client::getIo();

        // make the request
        $resp = $io->authenticatedRequest($req);

        // do we need to back off?
        if ($resp->getResponseHttpCode() !== 200)
        {
            if (!$this->_ok_to_recur($url))
            {
                // sheet, too much recursion
                throw new Exception('Failed to make request to URL: ' . $url . '; too much recursion');
            }

            // update our counter
            $this->_increment_recur_counter($url);

            // sleep
            usleep(rand(100, 1000));

            // recur
            return $this->_make_req($url, $json);
        }

        // set the body
        $body = $resp->getResponseBody();

        // parse the xml
        return $json ? json_decode($body) : $body;
    }

    private function _ok_to_recur($url)
    {
        if (!isset($this->_recur_counter[$url]))
        {
            return true;
        }

        return (int)$this->_recur_counter[$url] < $this->_recur_limit;
    }

    private function _set_up_client()
    {
        $this->_client = new Google_Client();

        // apply our score for google docs
        $this->_client->setScopes(array(
            'https://docs.googleusercontent.com/',
            'https://www.googleapis.com/auth/drive',
            'https://www.googleapis.com/auth/userinfo.email'
        ));

        $this->_client->setClientId(CLIENT_ID);
        $this->_client->setClientSecret(CLIENT_SECRET);
        $this->_client->setRedirectUri(REDIRECT_URI);
        $this->_client->setDeveloperKey(DEVELOPER_KEY);

        // oauth client
        $this->_client_oauth = new Google_Oauth2Service($this->_client);
    }

    private function _update($folder_id, $home_file_id)
    {
        // iterate over files and folders and create a files hash
        $files = $this->_iterate_over_files($folder_id, $home_file_id);

        // use our model to rebuild our pages
        $page_model = $this->load_model('page');

        return $page_model->rebuild($files);
    }
}

/**
* BaseGItem
*/
class BaseGItem
{
    public $g_id;
    public $title;
    public $name;
    public $last_update;

    public function __construct($item)
    {
        $this->g_id = $item->id;
        $this->title = $item->title;
        $this->name = preg_replace('/[^\w\d]+/im', '_', strtolower($item->title));
        $this->last_update = strtotime($item->modifiedDate);
    }
}

/**
* GFolder
*/
class GFolder extends BaseGItem
{
    public $children;

    public function __construct($item)
    {
        parent::__construct($item);
    }
}

/**
* GFile
*/
class GFile extends BaseGItem
{
    public $body;
    public $is_home;

    public function __construct($item, $home_file_id)
    {
        parent::__construct($item);

        $this->is_home = $item->id === $home_file_id ? 1 : 0;
    }
}