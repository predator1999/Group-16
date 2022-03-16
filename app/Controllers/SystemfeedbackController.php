<?php

class SystemfeedbackController{

    public function makeSystemFeedbacks(){
        Controller::validateForm(["feedback"]);
        $user_roles=Controller::accessCheck(["admin","organization","registered_user"]);
        $feedback = new Systemfeedback();
        $uid = $_SESSION["user"]["uid"];
        $data = ['uid' => $uid, 'feedback'=>$_POST['feedback']];
        $feedback->updateSystemFeedback($data);
        (new UserController)->addActivity("You gave system feedback");
        if($user_roles["organization"])
            Controller::redirect("/Organisation/dashboard");
        elseif($user_roles["registered_user"] || $user_roles["guest_user"])
            View::render("home",[],$user_roles);
    }

    public function getSystemFeedbacks($data){
        $systemFeedback=new Systemfeedback();
        $systemFeedbacks= $systemFeedback->renderSystemFeedbacks($data);
        return $systemFeedbacks;
    }

}