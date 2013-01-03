<?php
/**
* Admin_Controller class
*/
class Admin_Controller
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

            // check for a post request
            if (isset($_POST['folder']))
            {
                // update the site
                $this->_update($_POST['folder']);
            }

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
        header('location: ' . URL_PREFIX . '/admin/');
    }

    private function _edit()
    {
        // prepare a list of folders
        $data = $this->_make_req('https://www.googleapis.com/drive/v2/files?q=' . urlencode('"root" in parents and mimeType = "application/vnd.google-apps.folder"'));

        // prepare our folders array
        $folders = $data->items;

        // load the view
        require_once 'view/admin_view.php';
    }

    private function _get_document_contents($url)
    {
        // make the req
        return $this->_make_req($url, false);
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

    private function _iterate_over_files($folder_id)
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
                array_push($files, array(
                    'title' => $item->title,
                    'children' => $this->_iterate_over_files($item->id)
                ));
            }
            elseif ($item->mimeType === 'application/vnd.google-apps.document')
            {
                $export_links = (array)$item->exportLinks;

                array_push($files, array(
                    'title' => $item->title,
                    'content' => $this->_get_document_contents($export_links['text/html']),
                    'last_update' => strtotime($item->modifiedDate)
                ));
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
            usleep((1 << $n) * 1000 + rand(0, 1000));

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

    private function _update($folder)
    {
        // iterate over files and folders and create a files hash
        $files = $this->_iterate_over_files($folder);

        print_r($files);
    }
}