<?php
/* DON'T ENTER HERE - START */

/**
 * Portable DB V1.0.0
 * Jefri Herdi Triyanto | aku@jefriherditriyanto.com
 * https://github.com/jefripunza
 * Copyright 2021
 */

// Lines Pointer [starting from 0] [DON'T CHANGE]
$pointer_password = 16;
$pointer_data = 18;

/*********************************************** STORAGE ***********************************************/
//Password
$PASSWORD = "password"; // change this
//List Data
$DATA = '[]';
/*********************************************** STORAGE ***********************************************/
/* DON'T ENTER HERE - STOP*/

// Root path for Portable DB
// use absolute path of directory i.e: '/var/www/folder' or $_SERVER['DOCUMENT_ROOT'].'/folder'
$root_path = $_SERVER['DOCUMENT_ROOT'];

//Reset Data
$reset_data = '[]';

function generateRandomString($length = 20)
{
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * LES'T GET START !!!
 */
class pDB
{
    public $data;

    /**
     * @param @message
     */
    public function errorMessage($message, $solved)
    {
        $url = $_SERVER["PHP_SELF"];
        $msg = "Portable DB<br>Error: $message";
        if (substr($url, -1) == '/') {
            $url = rtrim($url, '/');
            $msg .= '<br>';
            $msg .= "<br>$solved";
            $msg .= '<br>Try again: <a href="' . $url . '">' . $url . '</a>';
        }
        die($msg);
    }

    public function __construct()
    {
        global $root_path, $DATA;

        // state
        $this->data = array();
        $data = false;

        if (strlen($DATA)) {
            $data = json_decode($DATA);
        } else {
            $this->errorMessage("Cannot load Data", 'Check the string value in $DATA whether it conforms to the JSON standard.');
        }

        if (is_array($data) && count($data)) {
            $this->data = $data;
        } else {
            $this->save();
        }
    }

    /**
     * @param $oldPassword
     * @param $newPassword
     * @return bool
     */
    public function changePassword($oldPassword, $newPassword)
    {
        //
    }

    function list() {
        if (isset($this->data)) {
            return $this->data;
        } else {
            $this->errorMessage("Cannot load Data", 'Check the string value in $DATA whether it conforms to the JSON standard.');
            return false;
        }
    }

    public function save()
    {
        // define
        global $pointer_data;
        $this_path_file = __FILE__;

        // gathering
        $post = $this->data;
        // print_r($post); //debug

        $var_value = var_export(json_encode($post), true);

        $config_string = '$DATA' . " = $var_value;" . chr(13) . chr(10); // 2

        if (is_writable($this_path_file)) {
            $lines = file($this_path_file); // write per lines
            if ($fh = @fopen($this_path_file, "w")) {
                for ($x = 0; $x < count($lines); $x++) {
                    if ($x == $pointer_data) { // location
                        @fputs($fh, $config_string, strlen($config_string)); // write data
                    } else {
                        @fputs($fh, $lines[$x], strlen($lines[$x])); // write backup lines
                    }
                }
                @fclose($fh);
                return true;
            } else {
                return false;
            }
        }
    }

    public function clear()
    {
        global $pointer_data, $reset_data;

        $this_path_file = __FILE__;
        $config_string = '$DATA' . " = '$reset_data';" . chr(13) . chr(10); // 2

        if (is_writable($this_path_file)) {
            $lines = file($this_path_file); // write per lines
            if ($fh = @fopen($this_path_file, "w")) {
                for ($x = 0; $x < count($lines); $x++) {
                    if ($x == $pointer_data) { // location
                        @fputs($fh, $config_string, strlen($config_string)); // write data
                    } else {
                        @fputs($fh, $lines[$x], strlen($lines[$x])); // write backup lines
                    }
                }
                @fclose($fh);
                return true;
            } else {
                return false;
            }
        }
    }
}

/*********************************************** MAIN PROGRAM ***********************************************/
$print = array();
$print["success"] = false;

if (isset($_GET['password'])) {
    if ($_GET['password'] == $PASSWORD) {

        if (isset($_GET['execute'])) {
            $data = new pDB();

            if ($_GET['execute'] == "list") {
                $print["success"] = true;
                $print["data"] = $data->list();
            } else if ($_GET['execute'] == "clear") {
                $print["success"] = $data->clear();
            } else if ($_GET['execute'] == "add") {
                if (isset($_POST)) {
                    $_POST['_id'] = generateRandomString();
                    $data->data[] = $_POST;

                    $print["success"] = $data->save();
                } else {
                    $print["message"] = "post required!";
                }
            } else if ($_GET['execute'] == "edit") {
                if (isset($_POST['id'])) {
                    $id = $_POST['id'];
                    unset($_POST['id']);

                    // convert stdClass to array
                    $data->data = json_decode(json_encode($data->data), true);

                    foreach ($data->data as $index => $list) {
                        if ($list['_id'] === $id) {
                            foreach ($_POST as $key => $value) {
                                $data->data[$index][$key] = $value;
                            }
                        }
                    }

                    $print["success"] = $data->save();
                } else {
                    $print["message"] = "id required!";
                }
            } else if ($_GET['execute'] == "delete") {
                if (isset($_POST['id'])) {
                    $id = $_POST['id'];

                    // convert stdClass to array
                    $data->data = json_decode(json_encode($data->data), true);

                    foreach ($data->data as $index => $list) {
                        if ($list['_id'] === $id) {
                            array_splice($data->data, $index, 1);
                        }
                    }

                    $print["success"] = $data->save();
                } else {
                    $print["message"] = "id required!";
                }
            } else {
                $print["message"] = "execute not available!";
            }

        } else {
            $print["message"] = "execute required!";
        }

    } else {
        $print["message"] = "password wrong!";
    }
} else {
    $print["message"] = "password required!";
}

// Print to JSON
echo json_encode($print);

/*********************************************** MAIN PROGRAM ***********************************************/
