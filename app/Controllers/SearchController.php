<?php

class SearchController
{

    function view()
    {
        $user_roles = Controller::accessCheck(["guest_user", "registered_user", "admin"]);
        View::render("searchPage", [], $user_roles);
    }

    public function searchAll()
    {
        foreach ($_POST as $key => $value) {
            if (is_string($_POST[$key]))
                $_POST[$key] = trim($_POST[$key]); //trim input
            if ($_POST[$key] == "")
                unset($_POST[$key]);
        }

        $event_model = new Events;
        $events = $event_model->query($_POST); //get queried events

        $event_details = array();
        if ($events != false)
            foreach ($events as $event) {
                $details = (array_merge($event_model->getDetails($event["event_id"]), $event)); //get event details
                array_push($event_details, $details);
            }
        echo json_encode($event_details, JSON_INVALID_UTF8_IGNORE);
    }

    public function searchOrganisation()
    {
        foreach ($_POST as $key => $value) {
            $_POST[$key] = trim($_POST[$key]);  //trim input
            if ($_POST[$key] == "")
                unset($_POST[$key]);
        }

        $org_model = new Organisation;
        $organisations = $org_model->query($_POST); //get queried organisation

        $organisation_details = array();
        if ($organisations != false)
            foreach ($organisations as $organisation) {
                if ($org = $org_model->getDetails($organisation["uid"])) { //get organisation details
                    $details = array_merge($org, $organisation);
                    array_push($organisation_details, $details);
                }
            }

        echo json_encode($organisation_details, JSON_INVALID_UTF8_IGNORE);
    }
}