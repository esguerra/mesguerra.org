;##############################################################################
;#
;#   .emacs
;#
;##############################################################################

;# osvetljeni oklepaji
(custom-set-variables
  ;; custom-set-variables was added by Custom.
  ;; If you edit it by hand, you could mess it up, so be careful.
  ;; Your init file should contain only one such instance.
  ;; If there is more than one, they won't work right.
 '(inhibit-startup-screen t)
 '(show-paren-mode t)
 '(show-paren-ring-bell-on-mismatch t)
 '(truncate-lines t)
 '(colum-number-mode t)
 '(default-justification 'full)
 )

;#############################################################################
;#   Default frame size: 80 characters wide
;#############################################################################
;(setq default-frame-alist '((top . 0) (left . 800) (width . 80) (height . 56)))
(setq default-frame-alist '((top . 0) (left . 800) (width . 80) (height . 46)))

;#############################################################################
; Use CC-MODE for C and C++ stuff
;#############################################################################

(fmakunbound 'c-mode)
(makunbound 'c-mode-map)
(fmakunbound 'c++-mode)
(makunbound 'c++-mode-map)
(fmakunbound 'c-style-alist)

(autoload 'c++-mode "cc-mode" "C++ Editing Mode" t)
(autoload 'c-mode   "cc-mode" "C Editing Mode" t)

(setq auto-mode-alist
      (append '(("\\.C$"  . c++-mode)
		("\\.cc$" . c++-mode)
		("\\.c$"  . c-mode)    ; to edit C code
		("\\.h$"  . c++-mode)   ; to edit C++ code
		("\\.f$"  . f90-mode)   ; to edit C++ code
		("\\.F$"  . f90-mode)   ; to edit C++ code
		("makefile.*\\'" . makefile-mode)
		) auto-mode-alist))

(require 'cc-mode)

(setq c-tab-always-indent nil)
(setq tab-width 4)

;     Define '_' as part of word.
(add-hook 'c-mode-hook '(lambda ()
			  (modify-syntax-entry ?_ "w")
			  ))


;#############################################################################
;#   Text coloring
;############################################################################
(set-foreground-color "white")
(set-background-color "black")
(set-cursor-color "green")
(set-mouse-color "yellow")
 
;-----------------------------------------------------------------------------
;    Font-lock-mode
;-----------------------------------------------------------------------------

(setq font-lock-maximum-size nil); No maximum size 

(setq font-lock-face-attributes
      '(
	(font-lock-comment-face "LightGoldenRod3") ;"orange2") ; "peru") ; "moccasin")
	(font-lock-string-face "OrangeRed2")       ; "LightSalmon")
	(font-lock-keyword-face "cyan3")
	(font-lock-function-name-face "LightSkyBlue4")
	(font-lock-variable-name-face "Goldenrod1")
	(font-lock-type-face "SpringGreen3")       ; "PaleGreen3")
	;(font-lock-reference-face "Chartreuse3") ;"Aquamarine4")
	))

(setq font-lock-maximum-decoration
      '(
	(t . 3)
	))
(global-font-lock-mode t)


;#############################################################################
;#   C style indentation
;#############################################################################
(setq-default c-indent-level 4)
(setq-default c-continued-statement-offset 4)
(setq-default c-argdecl-indent 0)
(setq-default c-brace-offset -4)
(setq-default c-label-offset -4)
(setq-default c-label-offset -4)
(global-set-key "\r" 'newline-and-indent)
(add-hook 'c-mode-hook
       (function (lambda ()
                   (setq comment-column 40))))

;#############################################################################
;#   Printer
;#############################################################################
;#(setq lpr-switches (list "-od"))

;#############################################################################
;#   Repeat last search
;#############################################################################
(defun repeat-last-search()
  "Repeat last search forward"
  (interactive)
  (search-forward (car search-ring)))
(global-set-key "\M-s" 'repeat-last-search)

;#############################################################################
;#   Makefile
;#############################################################################
(add-hook 'makefile-mode-hook
	  (function (lambda ()
		      (define-key makefile-mode-map "$"
			'self-insert-command)
		      )))

;#############################################################################
;#   Custom functions
;#############################################################################
(defun scroll-up-one ()
  "calls (scroll-up 1)"
  (interactive)
  (scroll-up 1))
(defun scroll-down-one ()
  "calls (scroll-down 1)"
  (interactive)
  (scroll-down 1))

(defun bozzo-next-error ()
  "Call next-error and report name of the function the error is in."
  (interactive)
  (next-error)
  (let ((fun (add-log-current-defun)))
    (if fun (message (concat "In function: " fun))
      (message "Huh? No function found."))
  ))

;#############################################################################
;#   Automatic horizontal scrolling
;#############################################################################
;(setq post-command-hook 'hscroll-point-visible)

;#############################################################################
;#   Keyboard customization
;#############################################################################

(setq tex-close-quote "\"")
(setq tex-open-quote "\"")
(global-set-key [delete] 'delete-char) ; spremenil 21.10.97
(global-set-key [hpDeleteChar] 'delete-char)             ; spremenil 21.10.97
(global-set-key [deletechar] 'delete-char)             ; spremenil 21.10.97
(global-set-key [M-backspace] 'undo)             ; KUndo         aBS
(global-set-key [home] 'beginning-of-line)       ; KBeginLine    Hom
(global-set-key [end] 'end-of-line)              ; KEndLine      End
(global-set-key [select] 'end-of-line)           ; KEndLine      End
(global-set-key [insertline] 'beginning-of-line)
(global-set-key [deleteline] 'end-of-line)
(global-set-key [C-insertline] 'beginning-of-buffer)
(global-set-key [C-deleteline] 'end-of-buffer)


; moving
(global-set-key [C-left] 'backward-word)         ; KPrevWord     cLf
(global-set-key [C-right] 'forward-word)         ; KNextWord     cRt

(global-set-key [C-up] 'backward-paragraph)      ; KPrevPara     cUp
(global-set-key [C-down] 'forward-paragraph)     ; KNextPara     cDn

(global-set-key [C-home] 'beginning-of-buffer)   ; KBeginData    cHom
(global-set-key [C-end] 'end-of-buffer)          ; KEndData      cEnd
(global-set-key [C-select] 'end-of-buffer)       ; KEndData      cEnd
(global-set-key [C-prior] 'scroll-right)         ; KPageRight    cPgUp
(global-set-key [C-next] 'scroll-left)           ; KPageLeft     cPgDn
(global-set-key [\C-delete] 'kill-word)
(global-set-key [\C-backspace] 'backward-kill-word)

(global-set-key [\M-left] 'backward-sexp)          ; expression
(global-set-key [\M-right] 'forward-sexp)

;;; This package gets to you ability to walk beetwen matching #if/#else/#endif
(global-set-key [\C-\M-up]   '(lambda() (interactive) (if-jump-jump 'backward)))
(global-set-key [\C-\M-down] '(lambda() (interactive) (if-jump-jump 'forward)))

(global-set-key [\C-\M-left] 'beginning-of-defun)  ; defun
(global-set-key [\C-\M-right] 'end-of-defun)



; Function keys
;(global-set-key [f1] 'point-to-register)
(global-set-key [f2] 'jump-to-register)
(global-set-key [f4] 'blink-matching-open)
(global-set-key [f5] 'vc-toggle-read-only)
(global-set-key [f6] 'vc-next-action)
(global-set-key [f7] 'next-error)
;(global-set-key [f8] 'kill-compilation)
(global-set-key [f9] 'gud-step)
(global-set-key [f10] 'gud-next)
(global-set-key [f12] 'goto-line)
(global-set-key [f8] 'indent-region)
(global-set-key [f1] 'goto-line)

(global-set-key [f3] 'ispell-region)

; Set mouse buttons to old values
(global-set-key [C-down-mouse-1] 'mouse-buffer-menu)
(global-set-key [C-down-mouse-3] 'mouse-set-font)

; misc
(global-set-key [\S-return] 'compile)
(global-set-key [\M-return] 'bozzo-next-error)
(global-set-key "\M-." 'find-tag-other-window)
(global-set-key [insertchar] 'overwrite-mode)
(global-set-key [\C-tab] 'other-window)
(global-set-key "\C-c\C-o" 'oo-browser)
(global-set-key [pause] 'font-lock-fontify-buffer)
(global-set-key [break] 'font-lock-fontify-buffer)  ; spremenjeno 21.10.1997
(global-set-key "\C-x\C-k" 'kill-buffer)
(global-set-key [C-deletechar] 'kill-region)         ; KCut        cDel

;#############################################################################
;#   Parenthesis matching
;#############################################################################
(load "paren")



;#############################################################################
;#   Default minor modes
;#############################################################################
(setq-default transient-mark-mode t)
(setq-default line-number-mode t)
(setq-default column-number-mode t)



;#############################################################################
;#   Text files
;#############################################################################
(setq auto-mode-alist (cons (cons "\\.txt$" 'indented-text-mode) auto-mode-alist))
(add-hook 'text-mode-hook
	  (function (lambda ()
		      (auto-fill-mode))))

;#############################################################################
;#   Imenu
;#############################################################################
(require 'imenu)
(setq imenu-max-items 30)
(global-set-key [S-down-mouse-1] 'imenu)

;-----------------------------------------------------------------------------
; SHELL
;-----------------------------------------------------------------------------
(setq auto-mode-alist
     (append '(
		("\\.kshrc" . shell-script-mode)
		("\\.profile" . shell-script-mode)
		("\\.cases$" . shell-script-mode)
		("\\.runs$" . shell-script-mode)
		) auto-mode-alist))


;#############################################################################
;#   CHARMM Coloring and Indentation Mode
;#############################################################################

(autoload 'charmm-mode "~/Config/charmm-mode.el" "Charmm input files" t)
(setq auto-mode-alist (cons '("\\.inp\\'\\|\\.str\\'\\|\\.charmm\\'" . charmm-mode) auto-mode-alist)) 



;#############################################################################
;#   Desktop
;#############################################################################
(custom-set-faces
  ;; custom-set-faces was added by Custom.
  ;; If you edit it by hand, you could mess it up, so be careful.
  ;; Your init file should contain only one such instance.
  ;; If there is more than one, they won't work right.
 '(default ((t (:foreground "white" :background "black"))))
 '(show-paren-match ((t (:background "blue"))))
 '(show-paren-mismatch ((t (:bold nil :foreground "gold" :background "red")))))

;;; Turn on Latin-2 both as input and output method (Emacs 20.*
;;; and onwards)
;(set-language-environment "Slovenian")

;(set-default-font "10x20")
(set-default-font "-*-courier-medium-r-*-*-18-*-*-*-*-*-*-*")  
;(set-default-font "-adobe-courier-medium-r-normal--0-0-75-75-m-0-iso8859-2")


(setq list-faces-sample-text:
        "abcèdefghijklmnopqrs¹tuvwxyz¾ ABCÈDEFGHIJKLMNOPRS©TUVWXYZ®")

;(set-language-environment "Latin-2")

;(load-file "/opt/intel/idb/10.1.008/bin/idb.el")

;(autoload 'python-mode "python-mode" "Python Mode." t)
;(add-to-list 'auto-mode-alist '("\\.py\\'" . python-mode))
;(add-to-list 'interpreter-mode-alist '("python" . python-mode))

;(add-hook 'python-mode-hook
;           (lambda ()
;             (set (make-variable-buffer-local 'beginning-of-defun-function)
;                  'py-beginning-of-def-or-class)
;             (setq outline-regexp "def\\|class ")))


;(setq load-path
;      (cons "/usr/share/emacs/site-lisp/python-mode" load-path)

(setq pdb-path '/usr/lib/python2.4/pdb.py
      gud-pdb-command-name (symbol-name pdb-path))

(defun word-count nil "Count words in buffer" (interactive)
(shell-command-on-region (point) (mark) "wc -w"))


