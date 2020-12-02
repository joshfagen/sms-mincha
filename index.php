<?php
include('db.php');
include_once('./twilio_config.php');

?>
<html>
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"/>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/additional-methods.min.js"></script>

    <link rel="shortcut icon" type="image/png" href="/favicon.png"/>

    <meta http-equiv="refresh" content="5">


</head>
<style>
    body {
        margin: 0;
        padding: 0;
        background-color: #f1f1f1;
    }

    .box {
        width: 80vw;
        height: 80vh;
        position: absolute;
        padding: 20px;
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 5px;
        margin: 0 auto;
        top: 10vh;
        left: 10vw;
    }


    form .error {
        color: #FFBABA;
    }

    .txt-m {
        font-size: 15vh;
        color: white;
        text-align: center;

    }

    #mincha-qa {
        font-size: 12vh;
        color: #ff331f;
        text-align: center;
      }

    .Header-m {
        margin: 0;
        position: absolute;
        top: 50%;
        -ms-transform: translateY(-50%);
        transform: translateY(-50%);
        padding: ,5%,,5%;
    }




</style>
<body>

<div class="container box">
    <br/>


    <?php
    $statement = $connection->prepare("SELECT * FROM guest_count WHERE date = '" . date('Y-m-d') . "' ");
    $statement->execute();
    $guest_counter = $statement->fetch(PDO::FETCH_ASSOC);
    $guest_count = $guest_counter['count'];

    $date = date("Y-m-d 00:00:00");
    $statement = $connection->prepare("SELECT distinct seid FROM reply where reply_msg = 'yes' and created_date >= '$date'");
    $statement->execute();
    $count_yes = $statement->rowCount();

    $total_join = $count_yes + $guest_count;

    ?>


    <div class="Header-m">
        <?php if ($total_join >= MINIMUM_REPLY_LIMIT)  { ?>
            <h1 class="txt-m" id="mincha-yes"> THERE IS MINCHA TODAY</h1>
        <?php } elseif (time() > strtotime('1:55 pm')) { ?>
            <h1 class="txt-m center" id="mincha-no"> THERE IS "NO" MINCHA TODAY</h1>
        <?php } else { ?>
            <h1 class="txt-m center" id="mincha-qa"> MINCHA NOT CONFIRMED. We Only Have "<?php echo  $total_join ?>" People </h1>
        <?php } ?>

    </div>
</div>


<script type="text/javascript" language="javascript">

    $("#mincha-no").parents('.box').css({ "backgroundColor": "#ff655b"})
    $("#mincha-yes").parents('.box').css({ "backgroundColor": "GREEN" })
    $("#mincha-qa").parents('.box').css({ "backgroundColor": "#fff27f"})

</script>

</body>
</html>
