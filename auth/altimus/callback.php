<?php
require('../../config.php');
require_once 'classes/Authentication.php';
require_once 'classes/Constants.php';
$dashboard = $CFG->wwwroot;

$email = $_REQUEST['email'];
$token = $_REQUEST['token'] ?? '';


try {

    $objAuth = new Authentication();
    $objUsuario = $objAuth->validateToken($email, $token);


    if ($objUsuario) {

        global $CFG, $DB;
        $username = $email ?? '';
        $user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id));


        if (empty($user)) {
            try {

                $password = Constants::SENHA_PADRAO;
                $senha = password_hash($password, PASSWORD_DEFAULT);
                $userdata = create_user_record($username, $senha, 'altimus');
                $userdata->firstname = $objUsuario->firstname;
                $userdata->lastname = $objUsuario->lastname ?? '- Usuário Altimus';
                $userdata->email = $objUsuario->email;
                $userdata->institution = 'Altimus';
                $userdata->idnumber = $objUsuario->id;
                $userdata->password = $senha;
                $userdata->city = 'Blumenau';
                $userdata->country = 'BR';


                $DB->update_record('user', $userdata);

                //Realizar a inscrição do aluno em todos os cursos
                auth_plugin_altimus::subscribe_course($username);

                complete_user_login($userdata);
                $urltogo = $CFG->wwwroot . '/my/';
                redirect($urltogo);

            } catch (\Exception $e) {
                throw new moodle_exception($DB->get_last_error() . ' ' . $e->getMessage());
            }
        }

        if (complete_user_login($user)) {
            $urltogo = $CFG->wwwroot . '/my/';
            redirect($urltogo);
        } else {
            echo "not login";
            die;
        }


    } else {

        die('Não é possível autenticar');

    }


} catch (Exception $e) {
    throw $e;
}

