<?php
/**
* Admin_Controller class
*/
class Admin_Controller
{
    private $_client;
    private $_client_oauth;
    private $_sess;

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
        if ($this->_client->getAccessToken())
        {
            // check for a post request
            if ($_POST)
            {
                // update the site
                $this->_update();
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
        $xml = $this->_make_req('https://docs.google.com/feeds/default/private/full/-/folder?v=3&showroot=true');

        // prepare our folders array
        $folders = array();

        foreach ($xml->entry as $entry)
        {
            $attrs = $entry->content->attributes();

            $folders[(string)$attrs['src']] = $entry->title;
        }

        // load the view
        require_once 'view/admin_view.php';
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

    private function _make_req($url)
    {
        // prepare the request
        $req = new Google_HttpRequest($url);

        // get the io client
        $client = $this->_client;
        $io = $client::getIo();

        // make the request
        $resp = $io->authenticatedRequest($req);

        // parse the xml
        return simplexml_load_string($resp->getResponseBody());
    }

    private function _set_up_client()
    {
        $this->_client = new Google_Client();

        // apply our score for google docs
        $this->_client->setScopes(array(
            'https://docs.google.com/feeds',
            'https://docs.googleusercontent.com/',
            'https://spreadsheets.google.com/feeds',
            'https://www.googleapis.com/auth/userinfo.email'
        ));

        $this->_client->setClientId(CLIENT_ID);
        $this->_client->setClientSecret(CLIENT_SECRET);
        $this->_client->setRedirectUri(REDIRECT_URI);
        $this->_client->setDeveloperKey(DEVELOPER_KEY);

        // oauth client
        $this->_client_oauth = new Google_Oauth2Service($this->_client);
    }

    private function _update()
    {

    }
}