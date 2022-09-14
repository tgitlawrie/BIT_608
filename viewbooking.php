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
// get all information from booking and customer table related to id
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
      <h1>Booking Details</h1>
    </div>
  </div>
  <div class="footer">
    <h2><a href='listbookings.php'>[Return to the Bookings list]</a><a href='index.php'>[Return to the main page]</a>
    </h2>
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
  </div>
  <?php
  include "footer.php";
  ?>
</div>