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
?>
<?php

// clean inputs
function cleanInput($data)
{
  return htmlspecialchars(stripslashes(trim($data)));
}

include "config.php"; //load in any variables
$DBC = mysqli_connect("127.0.0.1", DBUSER, DBPASSWORD, DBDATABASE);

//check if the connection was good
if (mysqli_connect_errno()) {
  echo "Error: Unable to connect to MySQL. " . mysqli_connect_error();
  exit; //stop processing the page further
}

//Query to read the the pizza data for select elements
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


if (isset($_POST['submit']) and !empty($_POST['submit']) and ($_POST['submit'] == 'Place Order')) {
  //input validation
  // //date, success stored in $date
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

  //validate extras
  if (isset($_POST['extras']) and is_string($_POST['extras'])) {
    $fn = cleanInput($_POST['extras']);
    $extras = (strlen($fn) > 255) ? substr($fn, 1, 255) : $fn; //check length and clip if too big   
  } else {
    $error++; //bump the error flag
    $msg .= 'Invalid extras  '; //append eror message
    $description = '';
  }

  //save the item data if the error flag is still clear
  if ($error == 0) {
    // orders table
    $orderIN = "INSERT INTO orders (customerID,orderDate,extras) VALUES (?,?,?)";
    $stmt = mysqli_prepare($DBC, $orderIN); //prepare the query
    mysqli_stmt_bind_param($stmt, 'iss', $_SESSION['userid'], $date, $extras);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    //get order ID from orders table to ensure its correct
    $idQuery = "SELECT MAX(orderID) FROM `orders` LIMIT 1";
    $getID = mysqli_query($DBC, $idQuery);
    $row = mysqli_fetch_assoc($getID);
    $orderID = $row['MAX(orderID)'];

    // orderlines
    $lineIN = "INSERT INTO orderlines (orderID,quantity,itemID) VALUES (?,?,?)";
    $lStmt = mysqli_prepare($DBC, $lineIN); //prepare the query
    for ($i = 1; $i <= 10; $i++) {
      if ($_POST['quantity' . $i . ''] > 0) {
        mysqli_stmt_bind_param(
          $lStmt,
          'iii',
          $orderID,
          $_POST['quantity' . $i . ''],
          $_POST['item' . $i . '']
        );
        mysqli_stmt_execute($lStmt);
      }
    }
    mysqli_stmt_close($lStmt);
    header(
      'Location: http://localhost/pizza/listorders.php',
      true,
      303
    );
  } else {
    echo "<h2>$msg</h2>" . PHP_EOL;
  }
}
?>
<div id="body">
  <div class="header">
    <div>
      <h1>Place an order</h1>
    </div>
  </div>
  <div class="footer">
    <?php
    echo '<h1>Pizza order for customer ' . $_SESSION['firstName'] . ' ' . $_SESSION['lastName'] . '</h1>';
    ?>
    <br />
    <form action="placeorder.php" id="order" method="POST">
      <label for="date">Order for (date & time)</label>
      <input type="text" name="date" id="date" required />
      <label for="extras">Extras:</label>
      <input type="text" id="extras" name="extras" />
      <hr />
      <h3>Pizzas for this order:</h3>
      <div id="container" style="min-height: 220px;">
        <!-- this container is target for dynamic elements -->
      </div>
      <input type="submit" name="submit" value="Place Order">
      <a href="http://localhost/pizza/">[Cancel]</a>
    </form>
  </div>
</div>






<?php

mysqli_free_result($result); //free any memory used by the query
mysqli_close($DBC); //close the connection once done

include "footer.php";
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
  defaultDate: currentDate,
  onChange: (selectedDates, dateStr, instance) => {
    const selectedDate = dateStr;
  },
});
</script>
<script type="text/javascript">
let options = <?php echo json_encode($options); ?>;
let itemNumber = 1;

//on window load add first selector
window.addEventListener("load", (event) => {
  if (itemNumber <= 1) newOrder();
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
    // newItem.id = `item${itemNumber}`;
    newItem.name = `item${itemNumber}`;
    newItem.value = "order";
    newItem.addEventListener("change", newOrder);

    // create new number object
    const newQty = document.createElement("input");
    newQty.type = "number";
    newQty.name = `quantity${itemNumber}`;
    newQty.style = "width: 30px";
    newQty.value = "0";
    newQty.min = "0";

    container.appendChild(newLabel);
    container.appendChild(newItem);
    container.appendChild(newQty);
    container.appendChild(newRemove);
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
    if (itemNumber <= 10) itemNumber++;
  }
}
</script>
</body>

</html>