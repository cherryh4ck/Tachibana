function cookieExists(name) {
  return document.cookie.split(';').some(item => item.trim().startsWith(name + '='));
}

if (cookieExists('postEliminado')) {
    document.cookie = "postEliminado=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    notify("Post eliminado", "exito");
}