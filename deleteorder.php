<?php
include "header.php";
include "checksession.php";
include "menu.php";
// loginStatus(); //show the current login status
checkUser();

include "config.php"; //load in any variables
$DBC = mysqli_connect("127.0.0.1", DBUSER, DBPASSWORD, DBDATABASE);


//check if the connection was good
if (mysqli_connect_errno()) {
  echo "Error: Unable to connect to MySQL. " . mysqli_connect_error();
  exit; //stop processing the page further
}

//function to clean input but not validate type and content
function cleanInput($data)
{
  return htmlspecialchars(stripslashes(trim($data)));
}

//do some simple validation to check if id exists
if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $id = $_GET['id'];
  if (empty($id) or !is_numeric($id)) {
    echo "<h2>Invalid food item ID</h2>"; //simple error feedback
    exit;
  }
}
//the data was sent using a formtherefore we use the $_POST instead of $_GET
//check if we are saving data first by checking if the submit button exists in the array
if (isset($_POST['submit']) and !empty($_POST['submit']) and ($_POST['submit'] == 'Delete')) {
  $error = 0; //clear our error flag
  $msg = 'Error: ';
  //itemID (sent via a form it is a string not a number so we try a type conversion!)    
  if (isset($_POST['id']) and !empty($_POST['id']) and is_integer(intval($_POST['id']))) {
    $id = cleanInput($_POST['id']);
  } else {
    $error++; //bump the error flag
    $msg .= 'Invalid Order ID '; //append error message
    $id = 0;
  }

  //save the food item data if the error flag is still clear and food item id is > 0
  if ($error == 0 and $id > 0) {
    try {
      $query = "DELETE FROM orderlines WHERE orderID=?";
      $stmt = mysqli_prepare($DBC, $query); //prepare the query
      mysqli_stmt_bind_param($stmt, 'i', $id);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);

      $query = "DELETE FROM orders WHERE orderID=?";
      $stmt = mysqli_prepare($DBC, $query); //prepare the query
      mysqli_stmt_bind_param($stmt, 'i', $id);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);


      header(
        'Location: http://localhost/pizza/listorders.php',
        true,
        303
      );
    } catch (mysqli_sql_exception $e) {
      echo "<pre>";
      var_dump($stmt);
      var_dump($_POST);
      var_dump($id);
      echo "</pre>";
    }
  } else {
    echo "<h2>$msg</h2>" . PHP_EOL;
  }
}


//query to get all the order information for a given order number
$query =
  'SELECT orders.orderID, extras, orderDate, pizza, quantity, firstname, lastname, customer.customerID 
    FROM orders, orderlines, fooditems, customer
    WHERE orders.orderID = orderlines.orderID
    AND orderlines.itemID = fooditems.itemID
    AND customer.customerID = orders.customerID
    AND orders.orderID = ' . $id . '';
$result = mysqli_query($DBC, $query);
$rowcount = mysqli_num_rows($result);
//makes sure we have the Order
if ($rowcount > 0) {
  $row = mysqli_fetch_assoc($result);
  //check user is authorized to delete this order #
  isAuth($row['customerID'], $_SESSION['userid']);

  $date = $row['orderDate'];
  $customer = $row['firstname'] . " " . $row['lastname'];
  $extras = $row['extras'];
  $pizza = $row['pizza'];
  $quantity = $row['quantity'];
?>
<div id="body">
  <div class="header">
    <div>
      <h1>Delete Order</h1>
    </div>
  </div>
  <div class="footer">
    <fieldset>
      <legend>Pizza order detail for order #<?php echo $id ?></legend>
      <dl>
        <dt>Date & time ordered for: </dt>
        <dd><?php echo $date ?></dd>
        <dt>Customer name:</dt>
        <dd><?php echo $customer ?></dd>
        <dt>Extras:</dt>
        <dd><?php echo $extras ?></dd>
        <dt>Pizzas:</dt>
        <dd><?php echo $pizza . ' X ' . $quantity; ?></dd>
        <?php while ($row = mysqli_fetch_assoc($result)) {
            echo "<dd>" . $row['pizza'] . ' X ' . $row['quantity'] . ",</dd>";
          }
          ?>
      </dl>
    </fieldset>
    <?php
  } else echo "<h2>No Order found!</h2>"; //suitable feedback
  mysqli_free_result($result); //free any memory used by the query
  mysqli_close($DBC); //close the connection once done
    ?>
    <form method="POST" action="deleteorder.php">
      <h2>Are you sure you want to delete this order?</h2>
      <input type="hidden" name="id" value="<?php echo $id; ?>">
      <input type="submit" name="submit" value="Delete">
      <a href="listorders.php">[Cancel]</a>
    </form>
    </table>
  </div>
</div>
<?php
  include "footer.php";
  ?>
</body>

</html>