<?php
include('db.php');
if (!isset($_SESSION['user'])) {
    header("Location:".BASE_URL."login.php");
}
?>
<html>
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
    <script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/additional-methods.min.js"></script>

    <link rel="shortcut icon" type="image/png" href="/favicon.png"/>



</head>
<style>
    body
    {
        margin:0;
        padding:0;
        background-color:#f1f1f1;
    }
    .box
    {
        width:1270px;
        padding:20px;
        background-color:#fff;
        border:1px solid #ccc;
        border-radius:5px;
        margin-top:25px;
    }

    form label,
    form input,
     {
        border: 0;
        margin-bottom: 3px;
        display: block;
        width: 100%;
    }
    form .error {
        color: #ff0000;
    }

</style>
<body>

<div class="container box" >
    <br />
    <div class="table-responsive">
        <div class="alert alert-info" id="msg_alert" style="display: none">
            <span id="send_msg_text"></span>
        </div>
        <div class="row">
            <div class="col-md-6">
        <form method="post" id="msg_form" name="msg_form" enctype="multipart/form-data" style="margin-bottom: 75px">
            <div class="form-group">
                <label class="col-md-2">Text :</label>
                <div class="col-md-10">
                    <textarea style="overflow: hidden" name="smstext" id="smstext" class="form-control" rows="3" onkeyup="countChar(this)"></textarea>
                    <div >Count : <b><span id="charNum">500</span></b> </div>
                    <div class="mt-radio-inline" style="margin-top: 10px">
                        <label class="mt-radio">
                            <input type="radio" name="status" id="active" value="active" checked=""> Active
                            <span></span>
                        </label>
                        <label class="mt-radio" style="margin-left: 5px">
                            <input type="radio" name="status" id="inactive" value="inactive"> Inactive
                            <span></span>
                        </label>
                        <label class="mt-radio" style="margin-left: 5px">
                            <input type="radio" name="status" id="all" value="all"> All
                            <span></span>
                        </label>
                        <label class="mt-radio" style="margin-left: 5px">
                            <input type="radio" name="status" id="get_call" value="get_call"> Who responded yes today  
                            <span></span>
                        </label>
                        <input type="hidden" name="operation" id="operation" value="Send"/>
                        <input id="send_sms" type="submit" class="btn btn-success pull-right" value="Send" />
                    </div>
                </div>
            </div>
        </form>
            </div><div class="col-md-6">
                <form method="post" id="frm_guest_count" name="frm_guest_count" enctype="multipart/form-data" style="margin-bottom: 75px">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="btn btn-success btn-md pull-right">Add guest count</div>
                            </div>
                            <div class="col-md-3">
                                <?php 
                                $statement = $connection->prepare("SELECT * FROM guest_count WHERE date = '".date('Y-m-d')."' ");
                                $statement->execute();
                                $guest_counter = $statement->fetch(PDO::FETCH_ASSOC);
                                $count = (int)$guest_counter['count'];
                                
                                ?>
                                <input type="text" name="counter" id="counter" class="form-control" value="<?php echo $count; ?>"/>
                            </div>
                            <div class="col-md-3">
                                <input id="btn_add" type="submit" class="btn btn-info btn-md" value="Add" />
                            </div>
                        </div>
                    </div>

                </form>
                <div class="row">
                    <div class="col-md-12">
                        <a href="<?php echo BASE_URL ?>send-sms.php" target="_blank" class="btn btn-primary">Send SMS</a>
                        <a href="<?php echo BASE_URL ?>notify.php" target="_blank" class="btn btn-success">Notify Members</a>
                        <a href="<?php echo BASE_URL ?>confirm_cancel.php" target="_blank" class="btn btn-info">Confirm or Cancel MSG</a>
<!--                        <a href="--><?php //echo BASE_URL ?><!--sub_confirm.php" target="_blank" class="btn btn-info">Confirm or Cancel MSG</a>-->
                    </div>
                </div>
            </div>
            </div>
        <?php
        $date = date("Y-m-d 00:00:00");
        $statement = $connection->prepare("SELECT distinct m_to FROM send_sms WHERE date_created >= '$date'");
        $statement->execute();
        $count_sent = $statement->rowCount();
    
        $statement = $connection->prepare("SELECT distinct seid FROM reply where reply_msg = 'yes' and created_date >= '$date'");
        $statement->execute();
        $count_yes = $statement->rowCount();
        $statement = $connection->prepare("SELECT distinct seid FROM reply where reply_msg = 'no' and created_date >= '$date'");
        $statement->execute();
        $count_no = $statement->rowCount();
        
        $statement = $connection->prepare("SELECT distinct m_to FROM send_sms LEFT JOIN reply ON reply.seid = send_sms.seid where send_sms.date_created >= '" . $date . "' AND reply.rid IS NULL ");
        $statement->execute();
        $count_not_replied = $statement->rowCount();
        ?>
        <div style="margin-top:20px;width: 200px;" class="alert alert-info">
            <h4>Reply Summary</h4>
            <div class="row" style="margin-top:20px;">
                <div class="col-md-7">Total Sent :  </div><div class="col-md-5"><b><?php echo $count_sent; ?></b> </div>
               
            </div>
            <div class="row" style="margin-top:20px;">
                <div class="col-md-7">Total Yes :   </div><div class="col-md-5"><b><?php echo $count_yes; ?> </b></div>
               
            </div>
            <div class="row" style="margin-top:20px;">
                <div class="col-md-7">Total No :    </div><div class="col-md-5"><b><?php echo $count_no; ?> </b></div>
               
            </div>
            <div class="row" style="margin-top:20px;">
                <div class="col-md-7">Not Replied : </div><div class="col-md-5"><b><?php echo $count_not_replied; ?></b>     </div>
            </div>
        </div>
        <div class="alert alert-info" id="del_alert" style="display: none">
            <span id="msg_delete"></span>
        </div>
        <br />
        <div align="right">
            <button type="button" id="add_button" data-toggle="modal" data-target="#userModal" class="btn btn-info btn-md">Add</button>
            <form method="post" action="insert.php" style="display: inline-block" >
                <button type="submit" name="logout" value="logout" class="btn">Log out</button>
            </form>
        </div>
        <br /><br />
        <table id="tbl_user_data" class="table table-bordered table-striped">
            <thead>
            <tr>
                <th >Name</th>
                <th >Reply</th>
                <th >Email</th>
                <th >Phone</th>
                <th >Get call</th>
                <th width="10%">Status</th>
                <th width="10%">Action</th>
            </tr>
            </thead>
        </table>
    </div>
</div>

<div id="userModal" class="modal fade">
    <div class="modal-dialog">
        <form method="post" id="user_form" name="user_form" enctype="multipart/form-data" autocomplete="off">
            <div class="modal-content">
                <div class="modal-header">
        <div class="alert alert-info" id="su_alert" style="display: none">
            <span id="msg"></span>
        </div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Add User</h4>
                </div>
                <div class="modal-body">
                    <label>Name</label>
                    <input style="color: black" type="text" name="name" id="name" class="form-control" />
                    <br />
                    <label>Email</label>
                    <input style="color: black" type="text" name="email" id="email" class="form-control" />
                    <br />
                    <label>Phone</label>
                    <input style="color: black" type="text" name="phone" id="phone" class="form-control" />
                    <br />
                    <div class="form-group" id="rm_radio">
                        <label>Status</label>
                        <div class="mt-radio-inline">
                            <label class="mt-radio">
                                <input type="radio" name="status" id="active" value="Y" checked=""> Active
                                <span></span>
                            </label>
                            <label class="mt-radio">
                                <input type="radio" name="status" id="deactive" value="N"> Inactive
                                <span></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group" id="call_radio">
                        <label>Get call</label>
                        <div class="mt-radio-inline">
                            <label class="mt-radio">
                                <input type="radio" name="get_call" id="call_yes" value="Y"> Yes
                                <span></span>
                            </label>
                            <label class="mt-radio">
                                <input type="radio" name="get_call" id="call_no" value="N"> No
                                <span></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="user_id" id="user_id" />
                    <input type="hidden" name="operation" id="operation" />
                    <input type="submit" name="action" id="action" class="btn btn-success" value="Add" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" id="mi-modal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Are you sure you want to delete this record?</h4>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default delete_rec" ">Yes</button>
                <button type="button" data-dismiss="modal" class="btn btn-primary" id="modal-btn-no">No</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" language="javascript">
    $('#add_button').click(function(){
        $('#user_form')[0].reset();
        $("#rm_radio").hide();
        $("#call_radio").hide();
        $('.modal-title').text("Add User");
        $('#action').val("Add");
        $('#operation').val("Add");
    });
    var _textCounter = 500;
    function countChar(val) {
        var len = val.value.length;
        if (len >= _textCounter) {
          val.value = val.value.substring(0, _textCounter);
        } else {
          $('#charNum').text(_textCounter - len);
        }
      };
    var dataTable = $('#tbl_user_data').DataTable({
        "processing":true,
        "serverSide":true,
//        "order":[],
        "ajax":{
            url:"fetch.php",
            type:"POST"
        },
        "columnDefs":[
            {
                "targets":[1,4],
                "orderable":false,
            },
        ],
        // columns: [
        //
        //     { data: "uid", orderable: true },
        //     { data: "name", orderable: true },
        //     { data: "email", orderable: true },
        //     { data: "phone", orderable: true },
        //     { data: "get_call", orderable: true },
        //     { data: "is_active", orderable: true },
        //     { data: "created_date", orderable: true },
        //     { data: "modify_date", orderable: true },
        // ],
        "pageLength": 50
        

    });

   var user_form = $("form[name='user_form']").validate({
        // Specify validation rules
        rules: {
            // The key name on the left side is the name attribute
            // of an input field. Validation rules are defined
            // on the right side
            name: "required",
            phone: {
                required: true,
                maxlength: 12
            },
//            email: {
//                // required: true,
//                // Specify that email should be validated
//                // by the built-in "email" rule
//                email: true
//            },
        },
        // Specify validation error messages
        messages: {
            name: {
                required: "Name is required"
            },
            phone: {
                required: 'Phone number is required',
                number: "Please enter a valid number"
            },
//            email: {
//                required: 'Email is required',
//                email: "Please enter a valid email address",
//            },
        },
        // Make sure the form is submitted to the destination defined
        // in the "action" attribute of the form when valid
        submitHandler: function (form) {
//                form.submit();
            var name = $('#name').val();
            var email = $('#email').val();
            var phone = $('#phone').val();

            $.ajax({
                url: "insert.php",
                method: 'POST',
                data: new FormData(form),
                contentType: false,
                processData: false,
                success: function (data) {

                    $("#su_alert").show()
                    $("#msg").text(data);

                    setTimeout(function () {
                        $('#user_form')[0].reset();
                        $('#userModal').modal('hide');
                        dataTable.ajax.reload();
                        $("#su_alert").hide()
                        //location.reload();
                    }, 1000);
                }
            });
        }
    });

    $("form[name='msg_form']").validate({
        rules: {
            smstext: {
                 required: true,
            },
        },
        // Specify validation error messages
        messages: {
            smstext: {
                required: 'Text is required',
            },
        },

        submitHandler: function (form) {
            $("#send_sms").val("Sending ...")
                    .attr('disabled',true);
            
            $.ajax({
                url: "insert.php",
                method: 'POST',
                data: new FormData(form),
                contentType: false,
                processData: false,
                success: function (data) {

                    $("#msg_alert").show();
                    $("#send_msg_text").html(data);
                    $("#smstext").val('');
                    $("#send_sms").val("Send").removeAttr('disabled');
                    setTimeout(function () {
                        $("#msg_alert").hide()
                    }, 5000);
                }
            });
        }
    });
    $("form[name='frm_guest_count']").validate({
        rules: {
            counter: {
                 required: true,
            },
        },
        // Specify validation error messages
        messages: {
            counter: {
                required: 'Count is required',
            },
        },

        submitHandler: function (form) {
            $("#btn_add").val("Adding ...")
                    .attr('disabled',true);
            
            $.ajax({
                url: "save_counter.php",
                method: 'POST',
                data: new FormData(form),
                contentType: false,
                processData: false,
                success: function (data) {

                    $("#msg_alert").show();
                    $("#send_msg_text").html(data);
                    $("#smstext").val('');
                    $("#btn_add").val("Add").removeAttr('disabled');
                    setTimeout(function () {
                        $("#msg_alert").hide()
                    }, 5000);
                }
            });
        }
    });

    $(document).on('click', '.update', function(){
//        user_form.resetForm();
        $('#user_form')[0].reset();
        $('#operation').val("Update");
        var user_id = $(this).attr("id");
        $("#rm_radio").show();
        $("#call_radio").show();
        $.ajax({
            url:"fetch_single.php",
            method:"POST",
            data:{user_id:user_id},
            dataType:"json",
            success:function(data)
            {
                console.log(data.get_call);
                $('#userModal').modal('show');
                $('#name').val(data.name);
                $('#email').val(data.email);
                $('#phone').val(data.phone);
                if(data.is_active == 'Y'){
                    $("#active").attr('checked', 'checked');
                }else{
                    $("#deactive").attr('checked', 'checked');
                }
                if(data.get_call == 'Y'){
                    $("#call_yes").prop('checked', true);
                }else{
                    $("#call_no").prop('checked', true);
                }
//                $('#is_active').val(data.is_active);
                $('.modal-title').text("Update User");
                $('#user_id').val(user_id);
                $('#action').val("Update");
                $('#operation').val("Update");
            }
        })
    });

    $(document).on('click', '.delete', function(){
        var user_id = $(this).attr("id");
        $(".delete_rec").attr("id",user_id);
        $("#mi-modal").modal('show');
    });

    $(document).on('click', '.delete_rec', function(){
        var user_id = $(this).attr("id");

            $.ajax({
                url:"delete.php",
                method:"POST",
                data:{user_id:user_id},
                success:function(data)
                {
                    $("#del_alert").show()
                    $("#msg_delete").text(data);
                    setTimeout(function () {
                        dataTable.ajax.reload();
                        location.reload();
                    }, 1000);
                }
            });

    });



</script>

</body>
</html>
