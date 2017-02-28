<?php
function redirect($url)
{
    if (strlen(session_id()) > 0) // if using sessions
        {
            session_regenerate_id(true); // avoids session fixation attacks
            session_write_close(); // avoids having sessions lock other requests
        }

    header('Location: ' . $url);

    exit();
}
?>