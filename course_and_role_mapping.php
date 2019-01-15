<?php

    $role_mapping = get_role_mapping_for_sync($pluginconfig);
    $course_mapping = get_course_mapping_for_sync($pluginconfig);

    $mapped_roles = array();
    $mapped_courses = array();

    foreach ($saml_courses as $key => $course) {
        $mapped_role = $mapped_course_id = null;
        if (function_exists('saml_hook_get_course_info')) {
             $regs = saml_hook_get_course_info($course);
             if ($regs) {

                list($match, $country, $domain, $course_id, $period, $role, $status) = $regs;

                if (!empty($course_id)) {

                    foreach ($role_mapping as $shortname => $values) {
                        if (in_array($role, $values)) {
                            $mapped_role = $shortname;
                            break;
                        }
                    }

                    foreach ($course_mapping as $id => $values) {
                        if (in_array($course_id, $values)) {
                            $mapped_course_id = $id;
                            break;
                        }
                    }

                    if (isset($status) && isset($mapped_role) && isset($mapped_course_id)) {

                        if (!in_array($mapped_role, $mapped_roles)) {
                            $mapped_roles[] = $mapped_role;
                        }

                        $mapped_courses[$mapped_role][$status][$mapped_course_id] = array('country' => $country,
                            'domain' => $domain,
                            'course_id' => $mapped_course_id,
                            'period' => $period,
                            'role' => $mapped_role,
                            'status' => $status,
                        );
                        if (!$any_course_active && $status == 'active') {
                              $any_course_active = true;
                        }
                    } else if (!isset($status)) {
                        $err['course_enrollment'][] = get_string('auth_saml_status_not_found' , 'auth_saml');
                    } else if (!isset($role)) {
                        $err['course_enrollment'][] = get_string('auth_saml_role_not_found' , 'auth_saml');
                    } else {
                        $str_obj = new stdClass();
                        $str_obj->course = '('.$course_id.' -- '.$period.')';
                        $str_obj->user = $username;
                        $err['course_enrollment'][] = get_string('auth_saml_course_not_found' , 'auth_saml', $str_obj);
                    }
                }
            }
        } else {
            $err['course_enrollment'][] = get_string('auth_saml_hook_not_defined' , 'auth_saml');
        }
    }

    unset($saml_courses);
