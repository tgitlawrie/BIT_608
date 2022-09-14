<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"
  integrity="sha512-K/oyQtMXpxI4+K0W7H25UopjM8pzq0yrVdFdG21Fh5dBe91I40pDd9A4lzNlHPHBIP2cwZuoxaUSX0GJSObvGA=="
  crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css"
  integrity="sha512-MQXduO8IQnJVq1qmySpN87QQkiR1bZHtorbJBD0tzy7/0U9+YIC93QWHeGTEoojMVHWWNkoCp8V6OzVSYrX0oQ=="
  crossorigin="anonymous" referrerpolicy="no-referrer" />
<?php
include "header.php";
include "checksession.php";
include "menu.php";
// loginStatus(); //show the current login status

checkUser();


include "config.php"; //load in any variables
$DBC = mysqli_connect("127.0.0.1", DBUSER, DBPASSWORD, DBDATABASE);

if (mysqli_connect_errno()) {
  echo "Error: Unable to connect to MySQL. " . mysqli_connect_error();
  exit; //stop processing the page further
};

//function to clean input but not validate type and content
function cleanInput($data)
{
  return htmlspecialchars(stripslashes(trim($data)));
}

//retrieve the itemid from the URL
if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $id = $_GET['id'];
  if (empty($id) or !is_numeric($id)) {
    echo "<h2>Invalid Order ID</h2>"; //simple error feedback
    exit;
  }
}

///Query to read the the pizza data for select elements
$query = 'SELECT itemID,pizza,pizzatype,price FROM fooditems';
$result = mysqli_query($DBC, $query);
$rowcount = mysqli_num_rows($result);
//makes sure we have food items
if ($rowcount > 0) {
  $options = array();
  while ($row = mysqli_fetch_assoc($result)) {
    array_push($options, $row);
  }
} else echo "<h2>No food items found!</h2>"; //suitable feedback

// get the order information for given order id
$query = 'SELECT 
    orders.customerID,
    orders.orderID, 
    orderDate, 
    extras, 
    orderlines.itemID, 
    pizza, 
    quantity,
    pizzatype,
    price
    FROM orders,orderlines,fooditems
    WHERE orderlines.itemID = fooditems.itemID
    AND orders.orderID = orderlines.orderID
    AND orders.orderID =?';

$stmt = $DBC->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$selRowcount = mysqli_num_rows($result);
$items = array();
//store rows in an array
for ($i = 0; $i < $selRowcount; $i++) {
  $row = mysqli_fetch_assoc($result);
  //check user is authorized to edit this order #
  isAuth($row['customerID'], $_SESSION['userid']);
  array_push($items, $row);
}
mysqli_free_result($result);
?>

<div id="body">
  <div class="header">
    <div>
      <h1>Edit Order</h1>
    </div>
  </div>
  <div class="body">
    <div class="footer" style="margin: 0 auto; width:30%;">
      <?php
      echo '<h2>Pizza order for customer '
        . $_SESSION['firstName'] . ' '
        . $_SESSION['lastName'] . '</h2>';
      ?>

      <br />
      <form action="editorder.php" method="POST">
        <label for="date">Order for (date & time)</label>
        <?php
        echo '<input type="text" name="date" id="date" value="' . $items[0]['orderDate'] . '" required />';
        ?>
        <br />
        <br />
        <label for="extras">Extras:</label>

        <?php
        echo '<input type="text" id="extras" name="extras" value="'
          . $items[0]['extras'] . '" size="50" />';
        ?>
        <hr />
        <h3>Pizzas for this order:</h3>
        <div id="container">
        </div>
        <br />
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="submit" name="submit" value="Update">
        <a href="listorders.php">[Cancel]</a>
      </form>
    </div>
  </div>
  <?php

  include "footer.php";
  //check if we are saving data first by checking if the submit button exists in the array
  if (isset($_POST['submit']) and !empty($_POST['submit']) and ($_POST['submit'] == 'Update')) {
    //validate incoming data - only the first field is done for you in this example - rest is up to you do

    if (isset($_POST['id']) and !empty($_POST['id']) and is_integer(intval($_POST['id']))) {
      $id = cleanInput($_POST['id']);
    } else {
      $error++; //bump the error flag
      $msg .= 'Invalid Order ID '; //append error message
      $id = 0;
    }
    // date, success stored in $date
    $error = 0; //clear our error flag
    $msg = 'Error: ';
    if (isset($_POST['date']) and !empty($_POST['date']) and is_string($_POST['date'])) {
      $date = $_POST['date'];
    } else {
      $error++; //bump the error flag
      $msg .= 'Invalid date  '; //append eror message
      $date = '';
      echo $msg;
    }

    //description
    $extras = cleanInput($_POST['extras']);

    // save the item data if the error flag is still clear and order id is > 0
    if ($error == 0 and $id > 0) {
      try {
        //orders
        $orderUpQ = 'UPDATE orders
        SET orderDate=?,extras=?
        WHERE orders.orderID=?';
        $ostmt = mysqli_prepare($DBC, $orderUpQ); //prepare the query
        mysqli_stmt_bind_param($ostmt, 'ssi', $date, $_POST['extras'], $id);
        mysqli_stmt_execute($ostmt);
        mysqli_stmt_close($ostmt);

        //get the line id's for comparison
        $query = 'SELECT * FROM orderlines WHERE orderID=?';
        $stmt = $DBC->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $itemrows = mysqli_num_rows($result);

        // update statement
        $lineUP = 'UPDATE orderlines
              SET itemID=?,quantity=?
              WHERE lineID=?';
        $update = mysqli_prepare($DBC, $lineUP); //prepare the query

        // insert statement
        $newline = 'INSERT INTO orderlines (orderID,quantity,itemID) VALUES (?,?,?)';
        $add = mysqli_prepare($DBC, $newline); //prepare the query

        //delete statement
        $removeLine = 'DELETE FROM orderlines WHERE lineID=?';
        $del = mysqli_prepare($DBC, $removeLine); //prepare the query
        //check each order row
        for ($i = 1; $i <= 10; $i++) {
          //get row result for item id
          $row = mysqli_fetch_assoc($result);
          if ($_POST['quantity' . $i . ''] > 0) {
            // if row exists update
            if ($row) {
              mysqli_stmt_bind_param(
                $update,
                'iii',
                $_POST['item' . $i . ''],
                $_POST['quantity' . $i . ''],
                $row['lineID']
              );
              mysqli_stmt_execute($update);
              // else insert new row
            } else {
              mysqli_stmt_bind_param(
                $add,
                'iii',
                $id,
                $_POST['quantity' . $i . ''],
                $_POST['item' . $i . '']
              );
              mysqli_stmt_execute($add);
            }
          }
          // if quantity changes to 0 remove row
          if ($_POST['quantity' . $i . ''] == 0) {
            mysqli_stmt_bind_param($del, 'i', $row['lineID']);
            mysqli_stmt_execute($del);
          }
        }
        // close all of the statments
        mysqli_stmt_close($update);
        mysqli_stmt_close($stmt,);
        mysqli_stmt_close($add);
        mysqli_stmt_close($del);
        mysqli_free_result($result);
        // redirect
        header(
          'Location: http://localhost/pizza/listorders.php',
          true,
          303
        );
      } catch (mysqli_sql_exception $e) {
        // leaving this here in rememberance of passed struggles
        echo '<pre>';
        var_dump($e);
        var_dump($id);
        var_dump($_POST['item' . $i . '']);
        var_dump($_POST['quantity' . $i . '']);
        echo '</pre>';
        exit;
      }
    } else {
      echo "<h2>$msg</h2>" . PHP_EOL;
    }
  }

  mysqli_close($DBC); //close the connection once done
  ?>

  <script>
  const currentDate = new Date();
  // stores flatpickr instance in calendar, altinput makes format readable, onchange defines behaviour when date changes
  const calendar = flatpickr("#date", {
    enableTime: true,
    dateFormat: "Y-m-d H:i",
    time_24hr: true,
    minDate: "today",
    maxDate: new Date().fp_incr(7), // 7 days from now
    onChange: (selectedDates, dateStr, instance) => {
      const selectedDate = dateStr;
    },
  });
  </script>
  <script type="text/javascript">
  let options = <?php echo json_encode($options); ?>;
  let items = <?php echo json_encode($items); ?>;
  let itemNumber = 1;
  const orders = items.length;

  console.log(items);

  //on window load add first selector
  window.addEventListener("load", (event) => {
    newOrder();
  });

  // function to create select boxes
  function newOrder() {
    //select
    const container = document.querySelector("#container");
    if (itemNumber <= 10) {
      //create new select dropdown and label object
      const newItem = document.createElement("select");
      const newLabel = document.createElement("label");
      const newRemove = document.createElement("button");
      newLabel.for = `item${itemNumber}`;
      newLabel.innerHTML = `item: ${itemNumber}: `;
      newItem.name = `item${itemNumber}`;
      newItem.id = `item${itemNumber}`;
      newItem.value = "order";
      newItem.addEventListener("change", newOrder);

      // create new number object
      const newQty = document.createElement("input");
      newQty.type = "number";
      newQty.name = `quantity${itemNumber}`;
      newQty.id = `quantity${itemNumber}`;
      newQty.style = "width: 30px";
      newQty.value = "0";
      newQty.min = "0";

      //create options for newItem
      //default option
      const defOption = document.createElement("option");
      defOption.value = "none";
      defOption.innerHTML = "none";
      defOption.selected = "selected";
      defOption.disabled;
      defOption.hidden;



      for (let i = 0; i < options.length; i++) {
        if (i === 0) newItem.appendChild(defOption);
        const newOption = document.createElement("option");
        newOption.value = options[i].itemID;
        newOption.innerHTML = `${options[i].pizza} $${options[i].price} ${options[i].pizzatype}`;
        // create defualt option
        newItem.appendChild(newOption);
      }

      // append the things
      container.appendChild(newLabel);
      container.appendChild(newItem);
      container.appendChild(newQty);
      container.appendChild(newRemove);

      const linebreak = document.createElement("br");
      container.appendChild(linebreak);
      container.appendChild(linebreak);
      // increase itemnumber until 10

      // remove item
      newRemove.innerHTML = "x";
      newRemove.addEventListener("click", () => {
        if (itemNumber > 2) {
          newItem.remove();
          newLabel.remove();
          newQty.remove();
          newRemove.remove();
          linebreak.remove();
          itemNumber--;
        }
      });
      if (itemNumber <= orders) {
        // make the ordered pizza selected
        const orderItem = document.querySelector(`#item${itemNumber}`);
        for (i = 0; i < orderItem.options.length; i++) {
          if (orderItem.options[i].value == items[itemNumber - 1].itemID) {
            orderItem.options[i].setAttribute('selected', true);
          }
        }

        // add order quantity
        const orderQ = document.querySelector(`#quantity${itemNumber}`);
        orderQ.type = "number";
        orderQ.name = `quantity${itemNumber}`;
        orderQ.id = `quantity${itemNumber}`;
        orderQ.style = "width: 30px";
        orderQ.value = items[itemNumber - 1].quantity;
        orderQ.min = "0";
      }
      if (itemNumber <= 10) {
        if (itemNumber <= orders) {
          itemNumber++;
          newOrder();
        } else {
          itemNumber++
        }
      }
    }
  }
  </script>
  </body>

  </html>