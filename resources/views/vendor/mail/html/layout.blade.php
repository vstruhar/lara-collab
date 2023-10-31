<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{{ config('app.name') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light dark">
<meta name="supported-color-schemes" content="light dark">
<style>
:root {
color-scheme: light dark;
supported-color-schemes: light dark;
}
::-moz-selection { /* Code for Firefox */
color: #ffffff;
background: #74C0FC;
}
::selection {
color: #ffffff;
background: #74C0FC;
}
@media only screen and (max-width: 600px) {
.inner-body {
width: 100% !important;
}

.footer {
width: 100% !important;
}
}

@media only screen and (max-width: 500px) {
.button {
width: 100% !important;
}

@media (prefers-color-scheme: dark) {
::-moz-selection { /* Code for Firefox */
color: #ffffff;
background: #192e42;
}
::selection {
color: #ffffff;
background: #192e42;
}
body {
background-color: #1a1c1e !important;
color: #c1c2c5;
}
h1 {
color: #CED4DA;
}
.wrapper {
background-color: #1a1c1e;
}
.header a {
color: #c1c2c5;
}
.body {
background-color: #1a1c1e;
border-bottom: 1px solid #373a40;
border-top: 1px solid #373a40;
}
.inner-body {
background-color: #1a1c1e;
border-color: #373a40;
}
.subcopy {
border-top: 1px solid #373a40;
}
.footer p,
.footer a {
color: red !important;
}
.table th {
border-bottom: 1px solid #CED4DA;
}
.table td {
color: #c1c2c5;
}
.panel {
border-left: #373a40 solid 4px;
}
.panel-content {
background-color: #1a1c1e;
color: #c1c2c5;
}
.panel-content p {
color: #c1c2c5;
}
}
</style>
</head>
<body>

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
{{ $header ?? '' }}

<!-- Email Body -->
<tr>
<td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: hidden !important;">
<table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<!-- Body content -->
<tr>
<td class="content-cell">
{{ Illuminate\Mail\Markdown::parse($slot) }}

{{ $subcopy ?? '' }}
</td>
</tr>
</table>
</td>
</tr>

{{ $footer ?? '' }}
</table>
</td>
</tr>
</table>
</body>
</html>
