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

//do some simple validation to check if id exists
$id = $_GET['id'];
if (empty($id) or !is_numeric($id)) {
  echo "<h2>Invalid Food item ID</h2>"; //simple error feedback
  exit;
}


//query to get all the order information for a given order number
$query =
  'SELECT orders.orderID, extras, orderDate, pizza, quantity, firstname, lastname, orders.customerID 
    FROM orders, orderlines, fooditems, customer
    WHERE orders.orderID = orderlines.orderID
    AND orderlines.itemID = fooditems.itemID
    AND customer.customerID = orders.customerID
    AND orders.orderID = ' . $id . '';
$result = mysqli_query($DBC, $query);
$rowcount = mysqli_num_rows($result);

?>
<div id="body">
  <div class="header">
    <div>
      <h1>Order Details</h1>
    </div>
  </div>

  <?php
  //makes sure we have the Food Item
  if ($rowcount > 0) {
    $row = mysqli_fetch_assoc($result);

    // check user is authorized
    isAuth($row['customerID'], $_SESSION['userid']);
  ?>
  <div class="footer" style="width:30%; min-height: 350px;">
    <fieldset>
      <legend>Pizza order detail for order #<?php echo $id; ?></legend>
      <dl>
        <dt>Date & time ordered for: </dt>
        <dd><?php echo $row['orderDate'] ?></dd>
        <dt>Customer name:</dt>
        <dd><?php echo $row['firstname'] . ' ' . $row['lastname'] ?></dd>
        <dt>Extras:</dt>
        <dd><?php echo $row['extras'] ?></dd>
        <dt>Pizzas:</dt>
        <dd><?php echo $row['pizza'] . ' X ' . $row['quantity'] ?>,</dd>

        <?php
          while ($row = mysqli_fetch_assoc($result)) {
            echo "<dd>"
              . $row['pizza'] . ' X ' . $row['quantity'] . ","
              . "</dd>";
          } ?>
      </dl>
    </fieldset>
  </div>
  <?php
  } else echo "<h2>No Order found!</h2>"; //suitable feedback

  mysqli_free_result($result); //free any memory used by the query
  mysqli_close($DBC); //close the connection once done
  ?>
  </table>
</div>
<?php
include "footer.php";
?>
</body>

</html>