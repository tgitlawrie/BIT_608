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

// prepare a query and send it to the server
$query = 'SELECT bookingID, firstname, lastname, telephone, bookingdate, people, customer.customerID 
          FROM booking, customer
          WHERE customer.customerID = booking.customerID';
$result = mysqli_query($DBC, $query);
$rowcount = mysqli_num_rows($result);

?>
<div id="body">
  <div class="header">
    <div>
      <h1>Current Bookings</h1>
    </div>
  </div>
  <div class="footer">
    <h2><a href='makebooking.php'>[Make a Booking]</a><a href="index.php">[Return to main page]</a></h2>
    <table border="1">
      <thead>
        <tr>
          <th>Booking (date & time, people</th>
          <th>Customer (Telephone)</th>
          <th>Action</th>
        </tr>
      </thead>
      <?php

      //makes sure we have food items
      if ($rowcount > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
          $id = $row['bookingID'];
          $date = $row['bookingdate'];
          $people = " (" . $row['people'] . ") ";
          $customer = $row['firstname'] . " " . $row['lastname'];
          $phone = " (T:" . $row['telephone'] . ") ";

          if ($row['customerID'] === $_SESSION['userid'] || isAdmin()) {
            echo '<tr><td>' . $date . $people . '</td><td>' . $customer . $phone . '</td>';
            echo '<td><a href="viewbooking.php?id=' . $id . '">[view]</a>';
            echo  '<a href="editbooking.php?id=' . $id . '">[edit]</a>';
            echo  '<a href="deletebooking.php?id=' . $id . '">[delete]</a></td>';
          }
        }
      } else echo "<h2>No Bookings found!</h2>"; //suitable feedback

      mysqli_free_result($result); //free any memory used by the query
      mysqli_close($DBC); //close the connection once done
      ?>

    </table>
  </div>
  <?php
  include "footer.php";
  ?>
</div>