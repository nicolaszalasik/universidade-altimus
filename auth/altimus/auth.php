<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/authlib.php');
require_once 'classes/Authentication.php';
require_once 'classes/Constants.php';

class auth_plugin_altimus extends auth_plugin_base
{


    public function __construct()
    {
        $this->authtype = 'altimus';
        $this->config = get_config('auth_altimus');
    }


    public function auth_plugin_altimus()
    {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    function user_login($username, $password)
    {

        global $CFG, $DB;

        //Validar login no Altimus


        $objAuth = new Authentication();
        $objUsuario = $objAuth->login($username, $password);


        if ($objUsuario == false) {

            $user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id));
            if ($user) {
                return validate_internal_user_password($user, $password);
            }

            return false;
        } else {


            //Verificar se usuario ja existe


            $user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id));
            if ($user) {



                //
                complete_user_login($user);
                //$password = Constants::SENHA_PADRAO;
                //$validate = validate_internal_user_password($user, $password);
                return true;

            }

            try {

                if (!$this->can_create_new_user()) {
                    throw new moodle_exception('cantcreatenewuser', 'auth_altimus');
                }

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
                $this->subscribe_course($username);

                complete_user_login($userdata);

                $urltogo = $CFG->wwwroot . '/my/';
                redirect($urltogo);

            } catch (\Exception $e) {
               //throw new moodle_exception($DB->get_last_error() . ' ' . $e->getMessage());
            }

            return true;
        }


    }

    static function subscribe_course($username)
    {

        global $CFG, $DB;

        $user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id), '*', MUST_EXIST);


        $query = "SELECT id, fullname, shortname from {course}";
        $courselist = $DB->get_records_sql($query);
        foreach ($courselist as $course) {

            $context = context_course::instance($course->id);
            $studentroleid = $DB->get_field('role', 'id', array('shortname' => 'student'));
            if (!is_enrolled($context, $user->id)) {
                // Not already enrolled so try enrolling them.
                if (!enrol_try_internal_enrol($course->id, $user->id, $studentroleid, time())) {
                    // There's a problem.
                    throw new moodle_exception('unabletoenrolerrormessage', 'langsourcefile');
                }
            }

        }

        return true;
    }

    function check_enrolment($courseid, $userid, $roleid, $enrolmethod = 'manual')
    {

        global $DB;
        $user = $DB->get_record('user', array('id' => $userid, 'deleted' => 0), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        //$roleid = $DB->get_field('role', 'id', array('shortname' => 'student'));

        $context = context_course::instance($course->id);
        if (!is_enrolled($context, $user)) {
            $enrol = enrol_get_plugin($enrolmethod);
            if ($enrol === null) {
                return false;
            }
            $instances = enrol_get_instances($course->id, true);
            $manualinstance = null;
            foreach ($instances as $instance) {
                if ($instance->name == $enrolmethod) {
                    $manualinstance = $instance;
                    break;
                }
            }
            if ($manualinstance !== null) {
                $instanceid = $enrol->add_default_instance($course);
                if ($instanceid === null) {
                    $instanceid = $enrol->add_instance($course);
                }
                $instance = $DB->get_record('enrol', array('id' => $instanceid));
            }
            $enrol->enrol_user($instance, $userid, $roleid);
        }
        return true;
    }


    function is_internal()
    {
        return false;
    }


    function can_create_new_user()
    {
        return true;
    }

    function can_change_password()
    {
        return true;
    }


    function callback_handler()
    {
        global $CFG, $DB, $SESSION;
        die('callback_handler');
    }


}


