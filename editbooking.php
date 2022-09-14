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

include "config.php"; //load in any variables
$DBC = mysqli_connect("127.0.0.1", DBUSER, DBPASSWORD, DBDATABASE);

//check if the connection was good
if (mysqli_connect_errno()) {
  echo "Error: Unable to connect to MySQL. " . mysqli_connect_error();
  exit; //stop processing the page further
}


if (isset($_POST['submit']) and !empty($_POST['submit']) and ($_POST['submit'] == 'Update')) {

  //validate date, success stored in $date
  $error = 0; //clear our error flag
  $msg = 'Error: ';
  if (isset($_POST['date']) and !empty($_POST['date']) and is_string($_POST['date'])) {
    $date = $_POST['date'];
  } else {
    $error++; //bump the error flag
    $msg .= 'Invalid date  '; //append eror message
    $date = '';
  } //end of date validation

  //validate people, success stored in $people
  if (
    isset($_POST['people'])
    and !empty($_POST['people'])
    and $_POST['people'] > 0 and $_POST['people'] <= 10
  ) {
    $people = $_POST['people'];
  } else {
    $error++; //bump the error flag
    $msg .= 'Invalid People  '; //append eror message
    $people = '';
  } // end of people validation

  //validate phone number, stored in $contact
  $pattern = "/^[0-9]?\d{3}-\d{3}-\d{4}$/";
  if (isset($_POST['contact']) and preg_match($pattern, $_POST['contact'])) {
    $contact = $_POST['contact'];
  } else {
    $error++; //bump the error flag
    $msg .= 'Invalid Phone Number  '; //append eror message
    $contact = '';
  }

  // save the data if clear of errors
  if ($error == 0) {
    $query = 'UPDATE booking
          SET bookingdate=?,people=?,telephone=?
          WHERE bookingID=?';
    $ostmt = mysqli_prepare($DBC, $query); //prepare the query
    mysqli_stmt_bind_param($ostmt, 'sisi', $date, $people, $contact, $_POST['id']);
    mysqli_stmt_execute($ostmt);
    mysqli_stmt_close($ostmt);
    mysqli_close($DBC); //close the connection once done
    header('Location: listbookings.php', true, 303);
  } else {
    echo "<h2>$msg</h2>" . PHP_EOL;
  }
} // end of post

//retrieve the booking from the URL
if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $id = $_GET['id'];
  if (empty($id) or !is_numeric($id)) {
    echo "<h2>Invalid Order ID</h2>"; //simple error feedback
    exit;
  }
}

// get all information from booking and customer table related to id
$query =
  'SELECT bookingdate, people, telephone
  FROM booking
  WHERE bookingID=' . $id;
$result = mysqli_query($DBC, $query);
$rowcount = mysqli_num_rows($result);
$row = mysqli_fetch_assoc($result);
mysqli_free_result($result);
?>
<div id="body">
  <div class="header">
    <div>
      <h1>Edit a Booking</h1>
    </div>
  </div>
  <div class="body">
    <div class="footer">
      <div class="contact">
        <form method="POST" style="color: #7A6666; margin-top: 60px">
          <label for="date">Booking Date & time: </label>
          <input name="date" id="date" required value="<?php echo $row['bookingdate']; ?>"
            onblur="this.value=!this.value?'YYYY-MM-DD HH-MM':this.value;" onfocus="this.select()"
            onclick="this.value='';">
          <label for="people">Part size(# people, 1-10</label>
          <input type="number" name="people" min="1" max="10" value="<?php echo $row['people']; ?>"
            style="width: 60px;">
          <label for="Email">Contact Number:</label>
          <input type="text" name="contact" value="<?php echo $row['telephone']; ?>"
            onblur="this.value=!this.value?'<?php echo $row['telephone']; ?>':this.value;" onfocus="this.select()"
            onclick="this.value='';" required>
          <input type="hidden" name="id" value="<?php echo $id; ?>">
          <input type="submit" name="submit" value="Update" id="submit">
        </form>
      </div>
    </div>
  </div>
  <?php
  include "footer.php";
  ?>
</div>
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