#!/usr/bin/env python3
# Settings to flaskify my homepage.
import os
from flask import Flask, render_template, url_for, send_file, render_template_string
from flaskext.markdown import Markdown


app = Flask(__name__)

def make_tree(path):
    tree = dict(name=os.path.basename(path), children=[])
    try: lst = os.listdir(path)
    except OSError:
        pass #ignore errors
    else:
        for name in lst:
            fn = os.path.join(path, name)
            if os.path.isdir(fn):
                tree['children'].append(make_tree(fn))
            else:
                tree['children'].append(dict(name=name))
    return tree


@app.route("/")
@app.route("/home")
def home():
    return render_template('home.html')

@app.route("/footer")
def footer():
    return render_template('footer.html')

@app.route("/tricks")
def tricks():
    return render_template('tricks.html', title='Computational Tricks and Treats')    

@app.route("/render")
def render():
    return render_template('render.html', title='Render')        

@app.route("/courses")
def courses():
    return render_template('courses.html', title='Courses')

@app.route("/craft")
def craft():
    return render_template('craft.html', title='Craft')        

@app.route("/documents")
def dirtree1():
    path = os.path.abspath(u'static/documents')
    return render_template('documents.html', tree=make_tree(path), title='Documents')

@app.route("/scripts")
def dirtree2():
    path = os.path.abspath(u'static/scripts')
    return render_template('scripts.html', tree=make_tree(path), title='Scripts')

@app.route("/tricks/pymol")
def pymol():
    return render_template('pymol.html', title='Pymol')

@app.route("/tricks/pymol/example1")
def example1():
    return render_template('example1.html', title='Pymol Example 1')

@app.route("/tricks/vmd")
def vmd():
    return render_template('vmd.html', title='Vmd')

@app.route("/tricks/chimera")
def chimera():
    return render_template('chimera.html', title='Chimera')

@app.route("/tricks/r")
def r():
    return render_template('r.html', title='R')

@app.route("/tricks/git")
def git():
    return render_template('git.html', title='git')

@app.route("/tricks/svn")
def svn():
    return render_template('svn.html', title='svn')

@app.route("/tricks/mysql")
def mysql():
    return render_template('mysql.html', title='mysql')

@app.route("/tricks/django")
def django():
    return render_template('django.html', title='django')

@app.route("/tricks/python")
def python():
    return render_template('python.html', title='python')

@app.route("/tricks/linux")
def linux():
    return render_template('linux.html', title='linux')

@app.route("/tricks/benchmarks")
def benchmarks():
    return render_template('benchmarks.html', title='benchmarks')

@app.route("/tricks/docker")
def docker():
    return render_template('docker.html', title='docker')

@app.route("/tricks/ssh")
def ssh():
    return render_template('ssh.html', title='ssh')

@app.route("/tricks/gpg")
def gpg():
    return render_template('gpg.html', title='gpg')

@app.route("/tricks/emacs")
def emacs():
    return render_template('emacs.html', title='emacs')

@app.route("/tricks/google")
def google():
    return render_template('google.html', title='google')

@app.route('/tricks/regexp')
def regexp():
    try:
        return send_file('static/regexp/regexp.pdf', attachment_filename='regexp.pdf')
    except Exception as exmsg:
        exmsg = render_template_string('The file regexp.pdf could not be found.')
        return exmsg

@app.route("/workshop")
def workshop():
    return render_template('workshop.html', title='Workshop in Colombia')     

@app.route("/pymolvsvmd")
def pymolvsvmd():
    return render_template('pymolvsvmd.html', title='Pymol vs. Vmd')

@app.route("/traj")
def traj():
    return render_template('traj.html', title='Trajectories')

@app.route("/randg")
def randg():
    return render_template('randg.html', title='R and Grace')

if __name__ == '__main__':
    app.jinja_env.auto_reload = True
    app.config['TEMPLATES_AUTO_RELOAD'] = True
    app.run(debug=False, host='0.0.0.0')
#    app.run(debug=False)
#    app.run(debug=True)

Markdown(app)
