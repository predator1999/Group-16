<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Public/assets/newstyles.css">
    <script src="https://kit.fontawesome.com/c119b7fc61.js" crossorigin="anonymous"></script>
    <title>Event Page</title>
</head>
<style>
.photo-container {
    height: 55%;
    text-align: center;
    position: relative;
    padding: 2rem 2rem 0rem 2rem;
}

.profile-pic {
    max-width: 10%;
    min-width: 60px;
    aspect-ratio: 1/1;
    background-color: gray;
    border: 1px solid white;
    position: absolute;
    overflow: hidden;
    top: 20px;
    left: 0;
    right: 0;
    margin: auto
}


.cover-place-holder {
    vertical-align: center;
    position: absolute;
    left: 0;
    right: 0;
    margin: auto;
    max-height: 300px;
    min-height: 100px;
    background-color: gray;
    overflow: hidden;
    aspect-ratio: 4/1.2;
    max-width: 80%;
}

.photo-element {
    object-fit: cover;
    height: 100%;
    width: 100%;
}

.nav-secondary {
    display: flex;
    align-items: center;
    justify-content: center;

}

.nav-secondary-bar {

    display: flex;
    align-items: center;
    overflow: auto;

}

.nav-active {
    background-color: #16c79a !important;
    color: white !important;
}

.image-upload {
    position: absolute;
    bottom: 11px;
    right: 11px;
}

.image-upload>input {
    display: none;

}

/* Designing for scroll-bar */
::-webkit-scrollbar {
    width: 2px;
    height: 8px;
    margin: 2rem;
}

/* Track */
/*     ::-webkit-scrollbar-track {
        background: gainsboro;
        border-radius: 5px;
        padding: 10px;
        margin: 1rem;
    } */

/* Handle */
::-webkit-scrollbar-thumb {
    background: #16c79a;
    border-radius: 5px;
    border: 1 px solid #16c79a;
    margin: 1rem;
    height: 1px;
    width: 10px;
}

/* Handle on hover */
::-webkit-scrollbar-thumb:hover {
    background: #16c79a;
}

table {
    width: 100%;
    table-layout: fixed;
}

td {
    text-align: center;
    padding: 1rem 0;
}

.event-card-details {
    display: flex;
    flex-direction: row;
}

h1,
h2 {
    margin: 0px;
}

.info {
    text-align: center;
}

.photo-upload-button{
    padding: 0.3rem;
    border-radius: 6px;
    background-color: white;
    opacity: 0.7;
}

@media screen and (max-width:767px) {
    h1 {
        font-size: 1.5rem;
    }

    .event-card-details {
        flex-direction: column;
    }
}
</style>
<?php include "nav.php" ?>

<body>

    <div class="photo-container">
        <div class="cover-place-holder cover border-round">
            <img src="<?= $cover_photo ?>" alt="" class="photo-element" styl>
            <?php if (($organization || $moderator) && ($_GET["page"] == "about")) { ?>
                <div class="image-upload hidden form">
                    <label for="file-input" class="photo-upload-button">
                        <i class="fas fa-edit"></i>
                    </label>
                    <input id="file-input" name="cover-photo[]" type="file" form="update-form" />
                </div>
            <?php } ?>
        </div>
    </div>


    <div class="flex-col flex-center margin-md">
        <h1 class="data"><?= $event_name ?></h1>
        <?php if (($organization || $moderator) && ($_GET["page"] == "about")) { ?>
            <label class="form hidden" for="">Event name</label>
            <input value="<?= $event_name ?>" type="text" name="event_name" form="update-form" class="form form-ctrl hidden" placeholder="Enter event name" required></input>
        <?php } ?>
    </div>


    <div class="nav-secondary">
        <div class="nav-secondary-bar margin-lg">
            <?php $page = $_GET["page"] ?>
            <a class="btn margin-side-md <?php if ($page == "about") echo "nav-active"; ?>" style=" margin-bottom:10px;"
                href="/Event/view?page=about&&event_id=<?= $_GET["event_id"] ?>">About</a>
            <a class="btn margin-side-md <?php if ($page == "gallery") echo "nav-active"; ?>"
                style=" margin-bottom:10px;"
                href="/Event/view?page=gallery&&event_id=<?= $_GET["event_id"] ?>">Gallery</a>
            <a class="btn margin-side-md <?php if ($page == "forum") echo "nav-active"; ?>" style=" margin-bottom:10px;"
                href="/Event/view?page=forum&&event_id=<?= $_GET["event_id"] ?>">Forum</a>
            <a class="btn margin-side-md <?php if ($page == "feedback") echo "nav-active"; ?>"
                style=" margin-bottom:10px;"
                href="/Event/view?page=feedback&&event_id=<?= $_GET["event_id"] ?>">Feedback</a>
            <?php if ($organization || $moderator) { ?>
            <a class="btn margin-side-md <?php if ($page == "volunteers") echo "nav-active"; ?>"
                style=" margin-bottom:10px;"
                href="/Event/view?page=volunteers&&event_id=<?= $_GET["event_id"] ?>">Volunteers</a>
            <a class="btn margin-side-md <?php if ($page == "timeline") echo "nav-active"; ?>"
                style=" margin-bottom:10px;" href="/Event/view?page=timeline&&event_id=<?= $_GET["event_id"] ?>">Work
                Timeline</a>
            <a class="btn margin-side-md <?php if ($page == "chat") echo "nav-active"; ?>" style=" margin-bottom:10px;"
                href="/Event/view?page=chat&&event_id=<?= $_GET["event_id"] ?>">Chat</a>
            <?php } ?>
            <?php if ($organization || $treasurer) { ?>
                <a class="btn margin-side-md <?php if ($page == "budget") echo "nav-active"; ?>" style=" margin-bottom:10px;" href="/Event/view?page=budget&&event_id=<?= $_GET["event_id"] ?>">Budget</a>
                <a class="btn margin-side-md <?php if ($page == "donations") echo "nav-active"; ?>" style=" margin-bottom:10px;" href="/Event/view?event_id=<?= $_GET["event_id"] ?>&&page=donations">Donations</a>
            <?php } ?>
            <?php if ($organization) { ?>
                <a class="btn margin-side-md <?php if ($page == "userroles") echo "nav-active"; ?>" style=" margin-bottom:10px;" href="/Event/view?page=userroles&&event_id=<?= $_GET["event_id"] ?>">User Roles</a>
            <?php } ?>
        </div>
    </div>

    <?php
    if (isset($_GET["page"]) && $_GET["page"] == "about") require __DIR__ . "/aboutEvent.php";
    elseif (isset($_GET["page"]) && $_GET["page"] == "gallery") require __DIR__ .  "/eventGallery.php";
    elseif (isset($_GET["page"]) && $_GET["page"] == "forum") require __DIR__ . "/forum.php";
    elseif (isset($_GET["page"]) && $_GET["page"] == "feedback") require __DIR__ . "/feedback.php";
    elseif (isset($_GET["page"]) && $_GET["page"] == "budget") require __DIR__ . "/budgeting.php";
    elseif (isset($_GET["page"]) && $_GET["page"] == "userroles") require __DIR__ . "/roles.php";
    elseif (isset($_GET["page"]) && $_GET["page"] == "timeline") require __DIR__ . "/workTimeline.php";
    elseif (isset($_GET["page"]) && $_GET["page"] == "donations") require __DIR__ . "/donateDetails.php";
    elseif (isset($_GET["page"]) && $_GET["page"] == "volunteers") require __DIR__ . "/volunteer.php";
    elseif (isset($_GET["page"]) && $_GET["page"] == "chat") require __DIR__ . "/chat/chatApp.php";

    ?>

<!-- complaint div -->
<?php include "complaint.php" ?>

</body>


<?php include "footer.php" ?>
<!-- Link Script for display input validation errors-->
<script src="/Public/assets/js/input_validation.js"></script>


<script>
    function resizeProfile() {
        var cover_height = (document.querySelector(".cover").offsetHeight);
        //var cover_width =(document.querySelector(".cover").offsetwidth);
        //document.document.querySelector(".cover").style.height= parseInt(cover_width)*2/7 + "px";
        document.querySelector(".photo-container").style.height = parseInt(cover_height) + "px";
    }


    function resize() {
        resizeProfile();
    }
    window.onload = resize();
    window.addEventListener("resize", resize);


    document.querySelector(".nav-active").scrollIntoView({
        behavior: 'auto',
        block: 'center',
        inline: 'center'
    });
</script>

</html>