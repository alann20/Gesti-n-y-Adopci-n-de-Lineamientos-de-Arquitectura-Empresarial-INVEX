INVEX Lineamientos - MySQL + XAMPP + PHP API

1) Copiar la carpeta del proyecto dentro de C:\xampp\htdocs\
   Ejemplo: C:\xampp\htdocs\invex-lineamientos\

2) Iniciar Apache y MySQL desde XAMPP.

3) Abrir phpMyAdmin: http://localhost/phpmyadmin/
   Importar database/invex_arquitectura.sql

4) Copiar/usar el HTML en la misma carpeta del proyecto, por ejemplo:
   C:\xampp\htdocs\invex-lineamientos\index.html

5) La API queda en:
   GET    /invex-lineamientos/api/lineamientos.php
   GET    /invex-lineamientos/api/lineamientos.php?codigo=LIN-ENT-001
   PUT    /invex-lineamientos/api/lineamientos.php
   GET    /invex-lineamientos/api/responsables.php?codigo=LIN-ENT-001
   POST   /invex-lineamientos/api/responsables.php
   PUT    /invex-lineamientos/api/responsables.php
   DELETE /invex-lineamientos/api/responsables.php?id=123

6) Abrir SIEMPRE el HTML por HTTP, no con doble clic file://
   http://localhost/invex-lineamientos/

Nota: config.php asume MySQL de XAMPP con usuario root y contraseña vacía. Si tu XAMPP tiene contraseña, cambiar api/config.php.
