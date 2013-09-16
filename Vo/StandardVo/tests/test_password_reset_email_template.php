<html>
<body>
Hello <?= $user->get('email'); ?>,<br />
Please click the link below to reset your password:<br />
http://orionstudiomadison.com/user/reset/<?= $resetKey; ?><br />
<br />
Best,<br />
Aaron
</body>
</html>