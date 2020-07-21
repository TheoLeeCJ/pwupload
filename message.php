<?php
  if (!empty($_POST["message"])) {
    if (strlen($_POST["message"]) > 8192) echo "Message too long";
    else {
      if (!file_exists("messages.txt")) {
        file_put_contents("messages.txt", "");
      }
      if (filesize("messages.txt") > 64000) echo "Too many messages, try again later.";
      else {
        echo "Your message has been recorded.";
        file_put_contents("messages.txt", file_get_contents("messages.txt") . "\n\n" . $_POST["message"]);
      }
    }
  }
?>
<html>
  <head>
    <title>Leave Message | PWupload</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <style>
      body { font-family: "Segoe UI", sans-serif; }
      input[type=text] { padding: 10px; width: 100vw; max-width: 500px; margin-top: 20px; font-size: 1rem; }
      button[type=submit] { margin-top: 5px; font-size: 1rem; padding: 5px; }
    </style>
  </head>
  
  <body>
    <form method="post">
      <h2>Leave A Message | PWupload</h2>
      <div>
      Your message can be anything - a bug report, remark or the like.
      <br>Keep in mind that messages are public.
      </div>
      <input type="text" name="message" placeholder="Message..." />
      <br><button type="submit">Submit</button>
    </form>
    
    <div>
      We aren't accepting messages (bug reports, remarks, etc) at the moment.
      <br>Check back later this year when more details about PW are released.
    </div>
  </body>
</html>