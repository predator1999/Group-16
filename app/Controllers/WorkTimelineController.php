<?php

class WorkTimelineController
{
    public function view($event_details)
    {
        $user_roles = Controller::accessCheck(["organization", "treasurer", "moderator",], $_GET["event_id"]); //access check for organization, treasurer & moderator
        $pagination= Model::pagination("work_timeline", 5, "WHERE event_id= :event_id", ["event_id"=> $_GET["event_id"]]);
        $data["tasks"] = (new Task)->getTask($_GET["event_id"]); //creating a new object and request data from MODEL through the getTask function
        $data = array_merge($data, $event_details, $pagination);
        View::render("eventPage", $data, $user_roles);
    }

    public function addTask()
    {
        Controller::validateForm(["start_date", "end_date", "task"], ["event_id"]); 
        Controller::accessCheck(["organization", "moderator"], $_GET["event_id"]); //access check for organisation & moderator
        $event = (new Events)->getDetails($_GET["event_id"]); //creating a new object and requesting data to it from the MODEL through the getDetails function
        (new UserController)->addActivity("You added a task to the timeline of {$event['event_name']}", $_GET["event_id"]); //inserting the new activity
        $id = (new Organisation)->getUserRoles($_GET["event_id"]); //creating a new object for Organization class and request for the user roles through the function getUserRoles
        for ($i = 0; $i < count($id); $i++) {
            if ($id[$i]["moderator_flag"] == 1) { //send notificiations to all moderators whenever a new task has been added
                (new UserController)->sendNotifications("New task has been added to {$event['event_name']}...!", $id[$i]["uid"], "event", "window.location.href='/event/view?page=timeline&&event_id={$_GET["event_id"]}'", $_GET["event_id"],"addNewTaskMail",["event_name" => $event['event_name']],"New task from{$event['event_name']}...!");
            }
        }
        $_POST["event_id"] = $_GET["event_id"];
        (new Task)->addTask($_POST); //requesting to perform an insert operation through the MODEL 
        Controller::redirect("/Event/view", ["page" => "timeline", "event_id" => $_POST["event_id"]]);
    }

    public function editTask()
    {
        Controller::validateForm(["start_date", "end_date", "task", "task_id"],["event_id"]);
        Controller::accessCheck(["organization", "moderator"], $_GET["event_id"]); //access check for organization & moderator
        $event = (new Events)->getDetails($_GET["event_id"]); //creating the new event object and retrieve data by the event_id
        (new UserController)->addActivity("You edited an existing task of {$event['event_name']}", $_GET["event_id"]); //insert a new activity
        $id = (new Organisation)->getUserRoles($_GET["event_id"]);
        for ($i = 0; $i < count($id); $i++){
            if ($id[$i]["moderator_flag"] == 1) {
                (new UserController)->sendNotifications("Some task details of {$event['event_name']} has been edited!", $id[$i]["uid"], "event", "window.location.href='/event/view?page=about&&event_id={$_GET["event_id"]}'", $_GET["event_id"],"editTaskMail",["event_name" => $event['event_name']],"Some task of {$event['event_name']} has been edited...!");
            }
        }
        $_POST["event_id"] = $_GET["event_id"];
        $task = new Task;
        $task->editTask($_POST); //requesting to perform an update operation through the MODEL
        Controller::redirect("/Event/view", ["page" => "timeline", "event_id" => $_POST["event_id"]]);
    }

    public function deleteTask()
    {
        Controller::validateForm(["task_id"], ["event_id"]);
        Controller::accessCheck(["organization", "moderator"], $_GET["event_id"]);
        $event = (new Events)->getDetails($_GET["event_id"]);
        (new UserController)->addActivity("You deleted an existing task of {$event['event_name']}", $_GET["event_id"]);
        $id = (new Organisation)->getUserRoles($_GET["event_id"]);
        for ($i = 0; $i < count($id); $i++){
            if ($id[$i]["moderator_flag"] == 1) { //sending notifications to all moderators
                (new UserController)->sendNotifications("Some task of {$event['event_name']} has been deleted!", $id[$i]["uid"], "event", "window.location.href='/event/view?page=about&&event_id={$_GET["event_id"]}'", $_GET["event_id"],"deleteTaskMail",["event_name" => $event['event_name']],"Some task of {$event['event_name']} has been deleted!");
            }
        }
        (new Task)->deleteTask($_POST["task_id"]); //requesting to perform an delete operation through the MODEL
        Controller::redirect("/Event/view", ["page" => "timeline", "event_id" => $_GET["event_id"]]);
    }

    public function completed()
    {
        Controller::validateForm([], ["event_id", "task_id"]);
        Controller::accessCheck(["organization", "moderator"], $_GET["event_id"]);
        $event = (new Events)->getDetails($_GET["event_id"]);
        (new UserController)->addActivity("Some changes has been done in work timeline of {$event['event_name']}", $_GET["event_id"]);
        $id = (new Organisation)->getUserRoles($_GET["event_id"]);
        for ($i = 0; $i < count($id); $i++){
            if ($id[$i]["moderator_flag"] == 1) { //sending notifications to all moderators
                (new UserController)->sendNotifications("Some task of {$event['event_name']} has been completed!", $id[$i]["uid"], "event", "window.location.href='/event/view?page=about&&event_id={$_GET["event_id"]}'", $_GET["event_id"],"completeTaskMail",["event_name" => $event['event_name']],"Some task of {$event['event_name']} has been completed!");
            }
        }
        (new Task)->completed($_GET["task_id"]);
        Controller::redirect("/Event/view", ["page" => "timeline", "event_id" => $_GET["event_id"]]);
    }
}
