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

//prepare a query and send it to the server
$query = 'SELECT orders.orderID, customer.customerID, orderDate, firstname, lastname 
  FROM orders, customer 
  WHERE orders.customerID = customer.customerID';

$result = mysqli_query($DBC, $query);
$rowcount = mysqli_num_rows($result);
?>
<div id="body">
  <div class="header">
    <div>
      <h1>Current Orders</h1>
    </div>
  </div>
  <div class="footer" style="min-height: 350px; width: 50%;
  margin: 0 auto;">
    <table border="1" style="margin: 0 auto;">
      <thead>
        <tr>
          <th>Orders (Date of order, Order number)</th>
          <th>Customer</th>
          <th>Action</th>
        </tr>
      </thead>
      <?php

      //makes sure we have food items
      if ($rowcount > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
          $id = $row['orderID'];
          $fullName = $row['lastname'] . ', ' . $row['firstname'];
          if ($row['customerID'] === $_SESSION['userid'] || isAdmin()) {
            echo '<tr><td>' . $row['orderDate'] . ' (' . $id . ')</td><td>' . $fullName . '</td>';
            echo     '<td><a href="vieworder.php?id=' . $id . '">[view]</a>';
            echo         '<a href="editorder.php?id=' . $id . '">[edit]</a>';
            echo         '<a href="deleteorder.php?id=' . $id . '">[delete]</a></td>';
          }
          echo '</tr>' . PHP_EOL;
        }
      } else echo "<h2>No orders Found</h2>"; //suitable feedback

      mysqli_free_result($result); //free any memory used by the query
      mysqli_close($DBC); //close the connection once done
      ?>
    </table>
  </div>
</div>
<?php
include "footer.php";
?>
</body>

</html>