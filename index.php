<!DOCTYPE html>

<!--
  TIME SPENT:
  * 11/23: 4H
  * 11/24: 3H
  * 11/25: 1H

    TOTAL: 8H
-->

<html>
  <head>
    <title>PHP Tip Calculator</title>
    <link rel="stylesheet" href="./index.css">
  </head>

  <body>

    <?php
      // initialize values and error messages
      $subtotal = $tipPercentage = 0;
      $split = 1;
      $subtotalError = $tipPercentageError = $splitError = '';

      // check if the form was submitted
      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // set the subtotal amount, or an error message
        if (DNE($_POST['subtotal'])) {
          $subtotalError = 'Subtotal amount is required.';
        } else {
          $possibleSubtotal = sanitize($_POST['subtotal']);
          if (!preg_match("/^-?\d+(?:\.\d{1,2})?$/", $possibleSubtotal)) {
            $subtotalError = 'Subtotal amount is formatted incorrectly.';
          } else if ($possibleSubtotal <= 0) {
            $subtotalError = 'Subtotal amount must be greater than 0.';
          } else {
            $subtotal = $possibleSubtotal;
          }
        }

        // set the tip percentage amount, or an error message
        if (DNE($_POST['tipPercentage'])) {
          $tipPercentageError = 'Tip percentage is required.';
        } else {
          $tipPercentage = sanitize($_POST['tipPercentage']);
          if ($tipPercentage == 'custom') {
            if (DNE($_POST['customTipPercentage'])) {
              $tipPercentageError = 'Custom tip percentage is required when custom option is selected.';
            } else {
              $possibleTipPercentage = sanitize($_POST['customTipPercentage']);
              if (!preg_match("/^-?\d+(?:\.\d+)?$/", $possibleTipPercentage)) {
                $tipPercentageError = 'Custom tip percentage is formatted incorrectly.';
              } else if ($possibleTipPercentage <= 0) {
                $tipPercentageError = 'Tip percentage must be greater than 0.';
              } else {
                $tipPercentage = $possibleTipPercentage / 100;
              }
            }
          }
        }

        // set the split amount, or an error message
        if (DNE($_POST['split'])) {
          $splitError = 'Split number is required.';
        } else {
          $possibleSplit = sanitize($_POST['split']);
          if (!preg_match("/^-?\d+(?:\.\d+)?$/", $possibleSplit)) {
            $splitError = 'Split number is formatted incorrectly.';
          } else if ($possibleSplit <= 0) {
            $splitError = 'Split number must be greater than 0.';
          } else if (fmod($possibleSplit, 1) > 0) {
            $splitError = 'Split number must be a whole number.';
          } else {
            $split = $possibleSplit;
          }
        }
      }

      // sanitizes all form input
      function sanitize($data) {
        return htmlspecialchars(stripslashes(trim($data)));
      }

      function DNE($number) {
        return empty($number) && $number !== '0';
      }
    ?>

    <div id="calculator">
      <h1>Tip Calculator</h1>
      <form
        method="post"
        action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

        <!-- SUBTOTAL INPUT -->
        <div
          id="subtotal"
          <?php if (!empty($subtotalError)) echo 'class="error"'; ?>>
          Bill subtotal: $
          <input
            type="text"
            name="subtotal"
            value="<?php echo isset($_POST['subtotal']) ? sanitize($_POST['subtotal']) : '' ?>">
          <?php if (!empty($subtotalError)): ?>
            <div class="errorMessage"><?php echo $subtotalError; ?></div> 
          <?php endif; ?>
        </div>

        <!-- TIP PERCENTAGE INPUT -->
        <div
          id="tipPercentage"
           <?php if (!empty($tipPercentageError)) echo 'class="error"'; ?>>
          <div>Tip percentage:</div>
          <?php
            $percentages = array(.1, .15, .2);
            $lastChecked = isset($_POST['tipPercentage']) ? $_POST['tipPercentage'] : .15;
            foreach ($percentages as $percentage): ?>
              <label>
                <input
                  type="radio"
                  name="tipPercentage"
                  value="<?php echo $percentage; ?>"
                  <?php if ($lastChecked == $percentage) echo 'checked' ?> />
                <?php echo ($percentage * 100) . '%'; ?>
              </label>
          <?php endforeach; ?>
          <label>
            <input
              type="radio"
              name="tipPercentage"
              value="custom"
              <?php if ($lastChecked == 'custom') echo 'checked' ?> />
            Custom: <input
              type="text"
              name="customTipPercentage"
              value="<?php echo isset($_POST['customTipPercentage']) ? sanitize($_POST['customTipPercentage']) : '' ?>">%
          </label>
          <?php if (!empty($tipPercentageError)): ?>
            <div class="errorMessage"><?php echo $tipPercentageError; ?></div> 
          <?php endif; ?>
        </div>

        <!-- SPLIT INPUT -->
        <div
          id="split"
           <?php if (!empty($splitError)) echo 'class="error"'; ?>>
           Split between <input
            type="text"
            name="split"
            value="<?php echo isset($_POST['split']) ? sanitize($_POST['split']) : '1' ?>"> people.
           <?php if (!empty($splitError)): ?>
            <div class="errorMessage"><?php echo $splitError; ?></div> 
          <?php endif; ?>
        </div>

        <!-- SUBMIT BUTTON -->
        <div id="submit">
          <input type="submit" name="submit" value="Submit">
        </div>
      </form>

      <?php
        $showTipAndTotal = !empty($subtotal) && !empty($tipPercentage)
          && empty($subtotalError) && empty($tipPercentageError);
        if ($showTipAndTotal) {
          setlocale(LC_MONETARY, 'en_US');
          // format subtotal
          $subtotalFormatted = formatMoney($subtotal);
          // calculate tip and format it
          $tip = $subtotal * $tipPercentage;
          $tipFormatted = formatMoney($tip);
          // calculate total and format it
          $total = $subtotal + $tip;
          $totalFormatted = formatMoney($total);
          // calculate split and format it
          $showSplit = !empty($split) && $split > 1;
          if ($showSplit) {
            $tipPerPerson = $tip / $split;
            $tipPerPersonFormatted = formatMoney($tipPerPerson);
            $totalPerPerson = $total / $split;
            $totalPerPersonFormatted = formatMoney($totalPerPerson);
          }
        }

        // format all monetary values the same way
        function formatMoney($number) {
          return money_format('%.2n', $number);
        }
      ?>

      <?php
        if ($showTipAndTotal): ?>
          <div id="resultContainer">
            <table id="result">
              <tr>
                <td>Subtotal:</td>
                <td><?php echo $subtotalFormatted ?></td>
              </tr>
              <tr>
              <tr>
                <td>Tip:</td>
                <td><?php echo $tipFormatted ?></td>
              </tr>
              <tr>
                <td>Total:</td>
                <td><?php echo $totalFormatted ?></td>
              </tr>
              <?php if ($showSplit): ?>
                <tr>
                  <td>Tip (per person):</td>
                  <td><?php echo $tipPerPersonFormatted ?></td>
                </tr>
                <tr>
                  <td>Total (per person):</td>
                  <td><?php echo $totalPerPersonFormatted ?></td>
                </tr>
              <?php endif; ?>
            </table>
          </div>
      <?php endif; ?>
    </div>

    <script>
      var handler = function() {
        document.querySelector('input[type="radio"][value="custom"]').checked = true;
      };
      document.querySelector('input[name="customTipPercentage"]')
        .addEventListener('click', handler, false);
    </script>

  </body>
</html>