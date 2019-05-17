#!/usr/bin/env python3
# Settings to flaskify my homepage.
import os
from flask import Flask, render_template, url_for
app = Flask(__name__)

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
def dirtree():
    path = os.path.abspath(u'static/documents')
    return render_template('documents.html', tree=make_tree(path), title='Documents')

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

@app.route("/tricks/pymol")
def pymol():
    return render_template('pymol.html', title='Pymol')

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




if __name__ == '__main__':
    app.run(debug=True)
