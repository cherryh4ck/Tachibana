function cookieExists(name) {
  return document.cookie.split(';').some(item => item.trim().startsWith(name + '='));
}

if (cookieExists('perfil_error')) {
    document.cookie = "perfil_error=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    notify("Tu cuenta está suspendida.", "error");
}