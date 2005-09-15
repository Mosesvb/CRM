<?php
/*******************************************************************************
 *
 *  filename    : EventEditor.php
 *  last change : 2005-09-10
 *  website     : http://www.terralabs.com
 *  copyright   : Copyright 2005 Todd Pillars
 *
 *  function    : Editor for Church Events
 *
 *  ChurchInfo is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 ******************************************************************************/
// table fields
//  event_id       int(11)
//  event_type     enum('CS', 'SS', 'VOL')
//  event_title    varchar(255)
//  event_desc     varchar(255)
//  event_text     text
//  event_start    datetime
//  event_end      datetime
//  inactive       int(1) default 0

require "Include/Config.php";
require "Include/Functions.php";
require "Include/Header.php";
$sPageTitle = gettext("Church Event Editor");


$sAction = $_POST['Action'];
$sOpp = $_POST['EID'];
if ($sAction = 'Edit' && !empty($sOpp))
{
        // Get data for the form as it now exists..
        $sSQL = "SELECT * FROM events_event as t1, event_types as t2 WHERE t1.event_type = t2.type_id AND t1.event_id ='".$sOpp."' LIMIT 1";

        $rsOpps = RunQuery($sSQL);

        $aRow = mysql_fetch_array($rsOpps, MYSQL_BOTH);
        extract($aRow);

        $aEventID = $event_id;
        $aTypeID = $type_id;
        $aEventName = $event_name;
        $aEventType = $type_name;
        $aEventTitle = $event_title;
        $aEventDesc = $event_desc;
        $aEventText = $event_text;
        $aStartTokens = explode(" ", $event_start);
        $aEventStartDate = $aStartTokens[0];
        $aStartTimeTokens = explode(":", $aStartTokens[1]);
        $aEventStartHour = $aStartTimeTokens[0];
        $aEventStartMins = $aStartTimeTokens[1];
        $aEndTokens = explode(" ", $event_end);
        $aEventEndDate = $aEndTokens[0];
        $aEndTimeTokens = explode(":", $aEndTokens[1]);
        $aEventEndHour = $aEndTimeTokens[0];
        $aEventEndMins = $aEndTimeTokens[1];
        $aEventStatus = $inactive;

} elseif (isset($_POST["SaveChanges"])) {
// Does the user want to save changes to text fields?
        $uEventID = $_POST['EventID'];
        $uEventType = $_POST['EventType'];
        if (strlen($_POST['EventTitle']) == 0 )
        {
                $uTypeErrors[$eET] = true;
                $bErrorFlag = true;
        }
        else
        {
                $uEventTitle = FilterInput($_POST['EventTitle']);
                $uTypeErrors[$eET] = false;
        }
        $uEventDesc = FilterInput($_POST['EventDesc']);
        $uEventText = FilterInput($_POST['EventText']);
        $uEventStart = $_POST['EventStartDate']." ".$_POST['EventStartTime'];
        $uEventEnd = $_POST['EventEndDate']." ".$_POST['EventEndTime'];
        $uEventStatus = $_POST['EventStatus'];

        // If no errors, then update.
        if (!$bErrorFlag)
        {
            $sSQL = "UPDATE events_event
                     SET `event_type` = '".$uEventType."',
                     `event_title` = '".$uEventTitle."',
                     `event_desc` = '".$uEventDesc."',
                     `event_text` = '".$uEventText."',
                     `event_start` = '".$uEventStart."',
                     `event_end` = '".$uEventEnd."',
                     `inactive` = '".$uEventStatus."'" .
                    " WHERE `event_id` = '" . $uEventID."';";
            RunQuery($sSQL);
        }
        header ("Location: ListEvents.php");
}

// Construct the form
?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" name="EventsEditor">
<input type="hidden" name="EventID" value="<?php echo $_POST['EID']; ?>">
<table cellpadding="3" width="75%" align="center">
  <caption>
    <h3><?php echo gettext("Editing Event ID: ".$aEventID); ?></h3>
    <input type="button" class="icButton" <?php echo 'value="' . gettext("Back to Menu") . '"'; ?> Name="Exit" onclick="javascript:document.location='Menu.php';">
    <?php if ($bErrorFlag) echo "There were errors"; ?>
  </caption>
  <tr>
    <td class="LabelColumn"><?php echo gettext("Event Type:"); ?></td>
    <td colspan="3" class="TextColumn">
      <select name="EventType">
<?php
// Get Event Names
$sSQL = "SELECT * FROM `event_types`";

        $rsOpps = RunQuery($sSQL);
        $numRows = mysql_num_rows($rsOpps);

        // Create arrays of the fundss.
        for ($row = 1; $row <= $numRows; $row++)
        {
                $bRow = mysql_fetch_array($rsOpps, MYSQL_BOTH);
                extract($bRow);

                echo '<option value="'.$type_id.'"';
                if ($aTypeID == $type_id) echo " selected";
                echo '">'.$type_name.'</option>';
        }
?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="LabelColumn"><?php echo gettext("Event Title:"); ?></td>
    <td colspan="3" class="TextColumn">
      <input type="text" name="EventTitle" value="<?php echo $aEventTitle; ?>" echo " size="40" maxlength="100">
      <?php if ( $bTitleError ) echo "<div><span style=\"color: red;\"><BR>" . gettext("You must enter a title.") . "</span></div>"; ?>
    </td>
  </tr>
  <tr>
    <td class="LabelColumn"><?php echo gettext("Event Desc:"); ?></td>
    <td colspan="3" class="TextColumn">
      <input type="text" name="EventDesc" value="<?php echo $aEventDesc; ?>" size="40" maxlength="100">
      <?php if ( $bDescError ) echo "<div><span style=\"color: red;\"><BR>" . gettext("You must enter a name.") . "</span></div>"; ?>
    </td>
  </tr>
  <tr>
    <td class="LabelColumn"><?php echo gettext("Event Sermon:"); ?></td>
    <td colspan="3" class="TextColumn"><textarea name="EventText" rows="10" cols="80"><?php echo $aEventText; ?></textarea></td>
  </tr>
  <tr>
    <td class="LabelColumn" <?php addToolTip("Format: YYYY-MM-DD<br>or enter the date by clicking on the calendar icon to the right."); ?>>
      <?php echo gettext("Start Date:"); ?>
    </td>
    <td class="TextColumn">
      <input type="text" name="EventStartDate" value="<?php echo $aEventStartDate; ?>" maxlength="10" id="SD" size="11">&nbsp;
      <input type="image" onclick="return showCalendar('SD', 'y-mm-dd');" src="Images/calendar.gif">
      <span class="SmallText"><?php echo gettext("[format: YYYY-MM-DD]"); ?></span>
    </td>
    <td class="LabelColumn">
      <?php echo gettext("Start Time:"); ?>
    </td>
    <td class="TextColumn">
      <select name="EventStartTime" size="1">
      <?php createTimeDropdown(7,18,15,$aEventStartHour,$aEventStartMins); ?>
      </select>
      &nbsp;<span class="SmallText"><?php echo gettext("[format: HH:MM]"); ?></span>
    </td>
  </tr>
  <tr>
    <td class="LabelColumn" <?php addToolTip("Format: YYYY-MM-DD<br>or enter the date by clicking on the calendar icon to the right."); ?>>
      <?php echo gettext("End Date:"); ?>
    </td>
    <td class="TextColumn">
      <input type="text" name="EventEndDate" value="<?php echo $aEventEndDate; ?>" maxlength="10" id="ED" size="11">&nbsp;
      <input type="image" onclick="return showCalendar('ED', 'y-mm-dd');" src="Images/calendar.gif">
      <span class="SmallText"><?php echo gettext("[format: YYYY-MM-DD]"); ?></span>
    </td>
    <td class="LabelColumn">
      <?php echo gettext("End Time:"); ?>
    </td>
    <td class="TextColumn">
      <select name="EventEndTime" size="1">
      <?php createTimeDropdown(7,18,15,$aEventEndHour,$aEventEndMins); ?>
      </select>
      &nbsp;<span class="SmallText"><?php echo gettext("[format: HH:MM]"); ?></span>
    </td>
  </tr>
  <tr>
    <td class="LabelColumn"><?php echo gettext("Event Status:"); ?></td>
    <td colspan="3" class="TextColumn">
      <input type="radio" name="EventStatus" value="0"<?php if ($aEventStatus == 0) echo " checked";?>> Active <input type="radio" name="EventStatus" value="1"<?php if ($aEventStatus == 1) echo " checked"; ?>> Inactive
      <?php if ( $bStatusError ) echo "<div><span style=\"color: red;\"><BR>" . gettext("Is this Active or Inactive?") . "</span></div>"; ?>
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input type="submit" name="SaveChanges" <?php echo 'value="' . gettext("Save Changes") . '"'; ?> class="icButton"></td>
  </tr>
</table>
</form>
<?php require "Include/Footer.php"; ?>