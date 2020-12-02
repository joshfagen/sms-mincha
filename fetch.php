<?php
include('db.php');
include('function.php');
$query = '';
$date = date('Y-m-d 00:00:00');
$output = array();
$query .= "SELECT m.*,r.reply_msg FROM member as m ";
$query .= " LEFT JOIN (select se.uid, MAX(r.rid) as rid FROM send_sms as se LEFT JOIN reply as r ON (r.seid = se.seid and r.created_date >= '$date') group by se.uid ) as t2 ON m.uid = t2.uid ";
$query .= " LEFT JOIN reply as r ON (r.rid = t2.rid and r.created_date >= '$date') ";
//echo $query;
//exit;

if (!empty($_POST["search"]["value"])) {
    $query .= ' WHERE m.name LIKE "%' . $_POST["search"]["value"] . '%" ';
    $query .= ' OR m.email LIKE "%' . $_POST["search"]["value"] . '%" ';
    $query .= ' OR m.phone LIKE "%' . $_POST["search"]["value"] . '%" ';
    $query .= ' OR m.get_call LIKE "%' . $_POST["search"]["value"] . '%" ';
    $query .= ' OR m.is_active LIKE "%' . $_POST["search"]["value"] . '%" ';
}

$cals = array(
    0 => 'uid',
    1 => 'name',
    2 => 'email',
    3 => 'phone',
    4 => 'get_call',
    5 => 'is_active',
    6 => 'created_date',
    7 => 'modify_date',
);

$query .= ' GROUP BY m.uid ';
if (!empty($_POST["order"]['0']['column'])) {
    $query .= ' ORDER BY ' . $cals[$_POST['order']['0']['column']] . ' '. $_POST['order']['0']['dir'] . ' ';
} elseif (!empty($_POST["order"]['0']['dir'])) {
    $query .= ' ORDER BY m.uid ' . $_POST['order']['0']['dir'] . ' ';
} else {
    $query .= ' ORDER BY m.uid ASC ';
}


if ($_POST["length"] != -1) {
    $query .= ' LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
}

$statement = $connection->prepare($query);
$statement->execute();
$result = $statement->fetchAll();
//echo "<pre>";
//print_r($statement);
//echo "</pre>";
//exit;
$data = array();
$filtered_rows = $statement->rowCount();
foreach ($result as $row) {
    if ($row["is_active"] == 'Y') {
        $status = 'Active';
    } else {
        $status = 'Inactive';
    }
    if ($row["get_call"] == 'Y') {
        $call_status = 'Yes';
    } else {
        $call_status = 'No';
    }

    $sub_array = array();
    $sub_array[] = $row["name"];
    $sub_array[] = ucfirst($row["reply_msg"]);
    $sub_array[] = $row["email"];
    $sub_array[] = $row["phone"];
    $sub_array[] = $call_status;
    $sub_array[] = $status;
    $sub_array[] = '<button type="button" name="update" id="' . $row["uid"] . '" class="btn btn-info btn-xs update"><i class="fa fa-edit"></i></button> '. '<button type="button" name="delete" id="' . $row["uid"] . '" class="btn btn-danger btn-xs delete"><i class="fa fa-trash"></i></button>';
    $data[] = $sub_array;
}
$output = array(
    "draw" => intval($_POST["draw"]),
    "recordsTotal" => $filtered_rows,
    "recordsFiltered" => get_total_all_records(),
    "data" => $data
);
echo json_encode($output);
?>