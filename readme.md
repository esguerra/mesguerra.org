# My homepage in flask
Flask is a so-called web application framework.
In turn flask uses WSGI (The Python Web Server Gateway Interface) to serve 
webpages. WSGI is a python effort to have a web-serving standard, similar to 
CGI. 

In order to make my webpage more responsive and have a little learning fun while
doing it I decided to use Flask. Yet the other part is to make it responsive, 
so, for now I'm just gonna go with good old bootstrap.

To run in in developer mode I have to do:

    FLASK_DEBUG=1 FLASK_APP=settings.py flask run

Inspired by Cody Shafer at:

<https://www.youtube.com/watch?v=QnDWIZuWYW0>

And Salvador Villalon at:

<https://medium.freecodecamp.org/how-to-build-a-web-application-using-flask-and-deploy-it-to-the-cloud-3551c985e492>

<https://codepen.io/craigvantonder/pen/MJwLWb>

And there's also a nice intro to CS class with flask as example at:  

<https://youtu.be/j5wysXqaIV8>

One thing left to do from the flaskification of my website is to use https. In 
one of the links there was even information as to how to use gunicorn and nginx 
and getting the https certificates, and perhaps putting it all into a yaml file 
or so.

Deploying has been even a greater challenge that migrating the website to flask 
and responsive with bootstrap.

This links sort of helped:

<https://github.com/defshine/flaskblog/blob/master/wsgi_gunicorn.py>
<https://www.digitalocean.com/community/tutorials/how-to-serve-flask-applications-with-gunicorn-and-nginx-on-ubuntu-18-04>

I was having issues with nginx.
I was using the card *default_server* in mesguerra.conf now, as it says so, 
this is a parameter to define the *default_server*, when there are many. 
So that was my mistake.

More battling.
So, the issue with responsiveness was that the following meta card
needed to be added.

<meta name="viewport" content="width=device-width, initial-scale=1">

Something really weird happened on wanting to restart supervisor and nginx when 
I made a change in the html of my website which is served by flask.
supervisor died, so, I had to change mesguerra.conf slightly and do:

    sudo supervisorctl reload

This worked, whereas:

    sudo supervisorctl restart
	
Didn't
Some website told me to add this to the mesguerra.conf

    startsecs = 0

Then for some reason even though in the virtualenv I was not able to install the
requirements.txt, so, in the end it boiled down to precisely this:

    pip3 install -r requirements.txt --user

Needless to say that this was an unpleasant time sink.


