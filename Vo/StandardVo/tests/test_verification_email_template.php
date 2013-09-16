<html>
<body>
Hello <?= $user->get('email'); ?>,<br />
Thanks for signing up for my new website. Please click the link below to verify your email address:<br />
http://orionstudiomadison.com/user/verify/<?= $verificationKey; ?><br />
<br />
Best,
Aaron
</body>
</html>