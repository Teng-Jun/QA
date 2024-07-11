# Pixiegram

**Team 17**

**Team Name:** Solid State Drive (SSD)

**Team Members:** 

Tan Jing Yuan (P5) - 2201241

Ivan Lee Sen Yao (P5) - 2200801

Sim Xin Rong (P6) - 2201660

Koh Yi Tong (P6) - 2201442

Koh Ming Yi (P6) - 2201656

Tay Hui Quan (P6) - 2201631

Aung Thura Zaw (P6) - 2200453

Lang Jun Feng (P6) - 2200566
 
**Environment Setup:**

1. Pull the code from GitHub.
2. Open it using Visual Studio 2022.
3. Open terminal within VS (Use cd to where your project folder is located e.g. cd "C:\Users\Kingston\Desktop\School Stuff\Pixiegram").
4. Run the command "composer install --no-dev" without the quotations.
5. (Only if you encounter some database problems, you might also have to change the .env file if your local MySQL root account has a password), run the command "php artisan migrate" to migrate database config/data information over before moving on to the next step.
6. Run the command "php artisan serve" without the quotations to launch the website locally.
7. Navigate to http://127.0.0.1:8000/ to access the program.

**Project Directory:**

1. Pixiegram/app/Http/Controllers - (Where all the controllers reside for backend logic).
2. Pixiegram/app/Models - (Where the models exist for data manipulation/rules).
3. Pixiegram/resources/views - (Where the views exist, for front-end changes, also in the layouts folder is where the navbar is located if you need to make any changes there).
4. Pixiegram/routes/web.php - (Where the routes are located, this is where you can edit/create routes and their logic of routing as well as which function to use from the controller if specified).

**Software Used:**

Laravel

MySQL

Jenkins

NGINX

Docker

GitHub Webhook

**Live Production:**

Pixiegram is now publicly hosted at: _https://pixiegram.ddns.net/_
