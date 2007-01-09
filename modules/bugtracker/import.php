<?php
$LSCurFile = __FILE__;

switch($_GET['step']) {
  default:
    $dsp->NewContent(t('Bugtracker Import'), t('Hier können Sie die bugs.xml-Datei Importieren, die Sie auf Ihrer Webseite exportiert haben'));
    $dsp->SetForm('index.php?mod=bugtracker&action=import&step=2', '', '', 'multipart/form-data');
		$dsp->AddFileSelectRow("importdata", $lang["install"]["import_import"], "");
    $dsp->AddFormSubmitRow('next');
    $dsp->AddContent();
  break;

  case 2:
    include_once('modules/install/class_import.php');
    $import = new import();
    $import->GetImportHeader($_FILES['importdata']['tmp_name']);

    $entrys = $xml->get_tag_content_array("entrys", $import->xml_content_lansuite);
    $entry = $xml->get_tag_content_array("entry", $entrys[0]);
    if ($entry) foreach ($entry as $entry_val) {
      $main = $xml->get_tag_content_array("main", $entry_val);
      $caption = $xml->get_tag_content("caption", $main[0]);
      $text = $xml->get_tag_content("text", $main[0]);
      $version = $xml->get_tag_content("version", $main[0]);
      $url = $xml->get_tag_content("url", $main[0]);
      $priority = $xml->get_tag_content("priority", $main[0]);
      $date = $xml->get_tag_content("date", $main[0]);
      $type = $xml->get_tag_content("type", $main[0]);
      $module = $xml->get_tag_content("module", $main[0]);
      $state = $xml->get_tag_content("state", $main[0]);
      $db->query("INSERT INTO {$config['tables']['bugtracker']} SET
        caption = '$caption',
        text = '$text',
        version = '$version',
        url = '$url',
        priority = ". (int)$priority .",
        date = '$date',
        type = ". (int)$type .",
        module = '$module',
        state = ". (int)$state .",
        reporter = ". (int)$auth['userid'] ."
        ");
      $bugid = $db->insert_id();

      $comments = $xml->get_tag_content_array("comments", $entry_val);
      $comment = $xml->get_tag_content_array("comment", $comments[0]);
      if ($comment) foreach ($comment as $comment_val) {
        $caption = $xml->get_tag_content("caption", $comment_val);
        $text = $xml->get_tag_content("text", $comment_val);
        $date = $xml->get_tag_content("date", $comment_val);
        $db->query("INSERT INTO {$config['tables']['comments']} SET
          caption = '$caption',
          text = '$text',
          date = '$date',
          relatedto_id = ". (int)$bugid .",
          relatedto_item = 'BugEintrag',
          creatorid = ". (int)$auth['userid'] ."
          ");
      }
    }
  break;
}
?>