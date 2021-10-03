<?php

class Events extends Model
{
    //add new event 
    public function addEvent($data)
    {
        $db = Model::getDB();
        $db->beginTransaction();

        $data["org_uid"] = $_SESSION["user"]["uid"];
        if ($data["longitude"] == "NULL" || $data["latitude"] == "NULL")
            $data["map"] = "false";
        else
            $data["map"] = "true";

        $query = "INSERT INTO `event` (`event_name`, `org_uid`, `latlang`, `start_date`,`end_date`,`start_time`,`end_time`, `about`,`mode`) VALUES (:event_name,  :org_uid, IF (STRCMP(:map, 'false')=0 ,NULL,POINT(:latitude ,:longitude)),:start_date,:end_date,:start_time, :end_time , :about ,:mode)";
        $params = array_intersect_key($data, ["event_name" => '', "org_uid" => '', "latitude" => '', "longitude" => '', "start_date" => '', "end_date" => '', "start_time" => '', "end_time" => '', "about" => '', "mode" => '', "map" => '']);

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $stmt->closeCursor();

        $select_query = "SELECT MAX(event_id) FROM event";
        $stmt = $db->prepare($select_query);
        $stmt->execute([]);
        $result = $stmt->fetchColumn();
        $stmt->closeCursor();
        $startDate = new DateTime($data['start_date']);
        $interval = new DateInterval('P1D');
        $realEnd = new DateTime($data['end_date']);
        $realEnd->add($interval);


        $period = new DatePeriod($startDate, $interval, $realEnd);

        foreach ($period as $date) {
            $event_date = $date->format('Y-m-d');
            $capacity_query = "INSERT INTO `volunteer_capacity`(`event_id`,`event_date`,`capacity`) VALUES (:event_id,:event_date,0)";
            $capacity_params = ["event_id" => $result, "event_date" =>  $event_date];
            $stmt = $db->prepare($capacity_query);
            $stmt->execute($capacity_params);
            $stmt->closeCursor();
        }
        
        $db->commit();
    }

    //remove an exsiting event
    public function remove($event_id)
    {
        $query = "UPDATE `event` SET status='deleted' WHERE event_id = :event_id";
        $params = ["event_id" => $event_id];
        Model::insert($query, $params);
    }

    //calling data from backend 
    public function getDetails($event_id)
    {
        $query = 'SELECT `event_id`, `event_name`, `org_uid`,`organisation_username`, ST_X(`latlang`) as latitude, ST_Y(`latlang`) as longitude, `start_date`,`end_date`, `start_time`, `end_time`, `about`, `mode`, `volunteer_capacity`, `donation_capacity`, `cover_photo`, `donation_status`, `volunteer_status`, `donations`, `volunteered`, `status`, volunteer_percent, donation_percent, TIMEDIFF(end_time, start_time) as duration  FROM event_details where event_id=:event_id ';
        $params = ["event_id" => $event_id];
        $result = Model::select($query, $params);

        if (count($result) >= 1)
            return $result[0];
        else
            return false;
    }

    //update details of an already exsting event
    public function updateDetails($data)
    {
        //...
        $db = Model::getDB();
        $db->beginTransaction();

        $params = array();
        if ($_FILES["cover-photo"]["size"][0] != NULL) {
            $time = (int)shell_exec("date '+%s'");
            exec("rm -rf /Users/visaljayathilaka/code/group-project/Group-16/app/Uploads/event/cover" . $data["event_id"] . "*");
            $cover_pic = new Image($data["event_id"] . $time, "event/cover/", "cover-photo", true);
            $params["cover_photo"] = $cover_pic->getURL();
        }

        if (isset($data["longitude"]) || isset($data["latitude"]))
            $data["map"] = "true";
        else
            $data["map"] = "false";


        //updating date, start time, duration, mode, description, event name, location, cover photo & status???    
        $old_data = $this->getDetails($data["event_id"]);
        $new_data = array_merge($old_data, $data);
        $update_data = array_intersect_key($new_data, ['event_id' => "", 'start_date' => "", 'end_date' => "", 'start_time' => "", 'end_time' => "", 'mode' => "", 'about' => "", 'event_name' => "", 'longitude' => "", 'latitude' => "", 'map' => '', 'cover_photo' => "", 'status' => ""]);
        $params = array_merge($update_data, $params);
        $volunteer = new Volunteer();
        $volunteered_uid = $volunteer->getVolunteeredUid($params["event_id"], $params["start_date"], $params["end_date"]);
        

        for ($i = 0; $i < count($volunteered_uid); $i++) {
            foreach ($volunteered_uid[$i] as $uid) {
                //    (new User)->sendNotification($uid);
                $delete_query = "DELETE FROM  volunteer WHERE uid = :uid AND event_id = :event_id";
                $delete_params = ['uid' => $uid, 'event_id' => $params["event_id"]];
                $stmt = $db->prepare($delete_query);
                $stmt->execute($delete_params);
                $stmt->closeCursor();
            }
        }

        $volunteer->checkVolunteerCapacityDateRange($params["event_id"], $params["start_date"], $params["end_date"]);
        $startDate = new DateTime($data['start_date']);
        $interval = new DateInterval('P1D');
        $realEnd = new DateTime($data['end_date']);
        $realEnd->add($interval);


        $period = new DatePeriod($startDate, $interval, $realEnd);

        foreach ($period as $date) {
            $event_date = $date->format('Y-m-d');
            $capacity_query = "INSERT IGNORE INTO `volunteer_capacity`(`event_id`,`event_date`,`capacity`) VALUES (:event_id,:event_date,0)";
            $capacity_params = ["event_id" => $params["event_id"], "event_date" =>  $event_date];
            $stmt = $db->prepare($capacity_query);
            $stmt->execute($capacity_params);
            $stmt->closeCursor();
        }

        $query = "UPDATE event SET `start_date` = :start_date, `end_date` = :end_date, `start_time`= :start_time, `end_time`= :end_time, `mode` = :mode, `about`=:about,`cover_photo` = :cover_photo, `status` = :status, `latlang`= IF (STRCMP(:map, 'false')=0 ,NULL,POINT(:latitude ,:longitude)) , `event_name` =:event_name WHERE `event_id`=:event_id ";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);

        $db->commit();
    }



    public function query($args)
    {
        $name = $city = $latitude = $longitude = $mode = $start_date = $org_uid = $distance = $order_type = $way = $status = $limit = $donation_capacity = $volunteer_capacity = $volunteer_status = $donation_status = NULL;
        extract($args, EXTR_OVERWRITE);
        $params = array();

        if ($city != NULL) {
            $city = trim($city);
            $city = str_replace(" ", "+", $city);
            $result = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=" . $city . ",LK&key=AIzaSyAN2HxM42eIrEG1e5b9ar2H_2_V6bMRjWk"), true)["results"][0]["geometry"]["location"];
            $longitude = $result["lng"];
            $latitude = $result["lat"];
        }
        //$query='SELECT event_id, ( 6371 * acos( cos( radians(6.848) ) * cos( radians( ST_X(latlang) ) ) * cos( radians( ST_Y(latlang) ) - radians(80.005) ) + sin( radians(6.848) ) * sin( radians( ST_X(latlang) ) ) ) ) AS distance FROM event';
        $query_select_primary = "SELECT * ";
        $query_select_secondary = ', ( 6371 * acos( cos( radians(:latitude) ) * cos( radians( ST_X(latlang) ) ) * cos( radians( ST_Y(latlang) ) - radians(:longitude) ) + sin( radians(:latitude2) ) * sin( radians( ST_X(latlang) ) ) ) ) AS distance ';
        $query_table = 'FROM event_details WHERE ';
        $query_filter_event_mode = ' mode=:mode AND ';
        $query_filter_date = ' start_date= :start_date AND ';
        $query_filter_organzation = ' org_uid =:org_uid AND ';
        $query_filter_status = ' status =:status AND ';
        $query_filter_name = ' event_name LIKE :name AND';
        $query_filter_volunteer_status = ' volunteer_status = :volunteer_status AND';
        $query_filter_donation_status = ' donation_status = :donation_status AND';
        $query_filter_volunteer_capacity = ' volunteer_capacity IS NOT NULL AND';
        $query_filter_donation_capacity = ' donation_capacity IS NOT NULL AND';
        $query_filter_last = ' 1=1 ';
        $query_filter_distance = ' distance=distance AND ';
        $query_filter_near = ' distance <= :distance AND ';
        $query_filter_limit = ' LIMIT :limit ';

        $query = $query_select_primary;

        if ($longitude != NULL && $latitude != NULL) {
            $query = $query . $query_select_secondary . $query_table;
            $params["latitude"] = $latitude;
            $params["longitude"] = $longitude;
            $params["latitude2"] = $latitude;
        } else
            $query = $query . $query_table;

        if ($mode != NULL) {
            $query = $query . $query_filter_event_mode;
            $params["mode"] = $mode;
        }


        if ($start_date != NULL) {
            $date_query = "(";
            $dates = explode(",", $start_date);
            for ($i = 0; $i < count($dates); $i++) {
                $date_query = $date_query . " start_date= :start_date$i";
                if ($i + 1 < count($dates))
                    $date_query = $date_query . " OR ";
                $params["start_date$i"] = $dates[$i];
            }
            $date_query = $date_query . " ) AND ";
            $query = $query . $date_query;

            /* $query = $query . $query_filter_date;
            $params["start_date"] = $start_date; */
        }

        if ($name != NULL) {
            $query = $query . $query_filter_name;
            $params["name"] = "%$name%";
        }

        if ($volunteer_status != NULL) {
            $query = $query . $query_filter_volunteer_status;
            $params["volunteer_status"] = $volunteer_status;
        }

        if ($donation_status != NULL) {
            $query = $query . $query_filter_donation_status;
            $params["donation_status"] = $donation_status;
        }


        if ($volunteer_capacity != NULL) {
            $query = $query . $query_filter_volunteer_capacity;
        }

        if ($donation_capacity != NULL) {
            $query = $query . $query_filter_donation_capacity;
        }

        if ($org_uid != NULL) {
            $query = $query . $query_filter_organzation;
            $params["org_uid"] = $org_uid;
        }

        if ($status != NULL) {
            $query = $query . $query_filter_status;
            $params["status"] = $status;
        }

        if ($order_type == 'volunteer_percent')
            $query = $query . ' volunteer_percent=volunteer_percent AND ';

        if ($order_type == 'donation_percent')
            $query = $query . ' donation_percent=donation_percent AND ';

        $query = $query . $query_filter_last . " HAVING ";

        /* if ($longitude != NULL && $latitude != NULL && $order_type == "distance") {
            $query = $query . $query_filter_distance;
        } */

        if ($longitude != NULL && $latitude != NULL && $distance != NULL) {
            $query = $query . $query_filter_near;
            $params["distance"] = $distance;
        }

        $query = $query . $query_filter_last;


        if ($order_type != NULL) {
            if ($order_type == "distance") {
                $params["longitude"] = $longitude;
                $params["latitude"] = $latitude;
            }
            $query = $query . " ORDER BY " . $order_type;
            if ($way == NULL)
                $way = 'DESC';
            $query = $query . " " . $way;
        }

        if ($limit != NULL) {
            $query = $query . $query_filter_limit;
            $params["limit"] = $limit;
        }

        /* var_dump($query);
        var_dump($params); */
        $result = Model::select($query, $params);

        if (count($result) == 0)
            return false;
        return $result;
    }
}
