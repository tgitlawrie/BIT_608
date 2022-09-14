<?php
include "header.php";
include "checksession.php";
include "menu.php";
// loginStatus(); //show the current login status
checkUser();

include "config.php"; //load in any variables
$DBC = mysqli_connect("127.0.0.1", DBUSER, DBPASSWORD, DBDATABASE);

//insert DB code from here onwards
//check if the connection was good
if (mysqli_connect_errno()) {
  echo "Error: Unable to connect to MySQL. " . mysqli_connect_error();
  exit; //stop processing the page further
}

//get tje id
$id = $_GET['id'];
if (empty($id) or !is_numeric($id)) {
  echo "<h2>Invalid booking ID</h2>"; //simple error feedback
  exit;
}

/// get all information from booking and customer table related to id
$query =
  'SELECT bookingdate, people, telephone, firstname, lastname 
  FROM booking, customer
  WHERE booking.customerID = customer.customerID
  AND bookingID=' . $id;
$result = mysqli_query($DBC, $query);
$rowcount = mysqli_num_rows($result);
$row = mysqli_fetch_assoc($result);
?>

<div id="body">
  <div class="header">
    <div>
      <h1>Delete Booking</h1>
    </div>
  </div>
  <div class="footer">
    <?php
    if ($rowcount > 0) {
      $date = $row['bookingdate'];
      $people = $row['people'];
      $customer = $row['firstname'] . " " . $row['lastname'];
      $contact = $row['telephone'];
    ?>
    <fieldset>
      <legend>Booking Detail # <?php echo $id; ?></legend>
      <dl>
        <dt>Booking date & time:</dt>
        <dd><?php echo $date; ?></dd>
        <dt>Customer name:</dt>
        <dd><?php echo $customer; ?></dd>
        <dt>Party size:</dt>
        <dd><?php echo $people; ?></dd>
        <dt>Contact number:</dt>
        <dd><?php echo $contact; ?></dd>
      </dl>
    </fieldset>
    <?php
    } else {
      echo "<h2>No Booking With that ID Found!</h2>";
    }
    ?>
    <form method="POST">
      <h2>Are you sure you want to delete this booking?</h2>
      <input type="hidden" name="id" value="<?php echo $id; ?>">
      <input type="submit" name="submit" value="Delete">
      <a href="listbookings.php">[Cancel]</a>
    </form>
  </div>

  <?php

  if (isset($_POST['submit']) and !empty($_POST['submit']) and ($_POST['submit'] == 'Delete')) {

    $query = "DELETE FROM booking WHERE bookingID=?";
    $stmt = mysqli_prepare($DBC, $query); //prepare the query
    mysqli_stmt_bind_param($stmt, 'i', $_POST['id']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    mysqli_free_result($result);
    mysqli_close($DBC);

    header(
      'Location: listbookings.php',
      true,
      303
    );
  }


  include "footer.php";
  ?>
</div>