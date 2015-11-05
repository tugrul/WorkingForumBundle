WorkingForumBundle
==================

ENGLISH
=================
** STILL IN DEVELOPMENT **
A forum bundle for Symfony 2, easy to use and fully fonctionnal
This bundle work with your user bundle with no extra configuration (which can extended FOSUserBundle)

Demo
-------------
Coming soon


Functionnalities
------------------
- Forum with subforum
- Use a moderator role as ROLE_MODERATOR (and default admin roles)
- Post editor using markdown with instant preview
- Enable topic locking
- Support multi language
- Enable marking topic as 'resolved'
- Automatic breadcrumb
- Messages counting (user, forum, suforum) with last replies
- Automatic pagination on topic list and topic


Setup
------------------
This bundle use KnpPaginatorBundle for pagination, KnpMarkdown for backend markdown rendering
Add to your composer.json, section 'require'
````json
"require" : {
        [...]
        "yosimitso/workingforumbundle" : "dev-master",
        "knplabs/knp-paginator-bundle": "2.4.*@dev",
        "knplabs/knp-markdown-bundle": "~1.3"
    }
```


Register the bundles in your AppKernel
Add to your app/config.yml

````yml
yosimitso_forum:
    topic_per_page: 10
    post_per_page: 5
    date_format: 'd/m/Y H:i:s'
```    
You can override the translations files

Your User Entity need these properties with getter and setter :
````php
       /**
     * @var integer
     * @ORM\Column(name="nb_post", type="integer")
     */
	 protected $nbPost;
 /**   
         * @var string
         * @ORM\Column(name="avatar_url", type="string",nullable=true)
         */
   
        protected $avatarUrl;
 /**   
         * @var string
         * @ORM\Column(name="username", type="string",nullable=true)
         */
   
        protected $username;
```

Todo
-----------
- Removing post by a moderator

FRANCAIS
==================
** EN DEVELOPEMENT **
Un bundle pour forum pour Symfony 2, simple a mettre en place et pleinement fonctionnel
Ce bundle utilise votre bundle utilisateur (qui peut hérité de FOSUserBundle)


Demo
-------------
Bientôt


Fonctionnalités
------------------
- Forum avec sous-forum
- Utilise un role modérateur ROLE_MODERATOR (également les roles admin par défaut)
- L'éditeur de message utilise markdown avec la prévisualisation instantanée
- Les topics peuvent être verrouillés
- Support le multilangage
- Les topics peuvent être marqués comme résolus
- Breadcrumb (fil d'Arianne) automatique
- Compteur de messages (utilisateur, forum, suforum) avec dernières réponses
- Pagination automatique sur la liste des topic, et les messages des topicq


Installation
------------------
Ce bundle utilise KnpPaginatorBundle pour la pagination, KnpMarkdown pour le parsage du markdown dans le backend
Ajoutez à votre composer.json, section 'require'
````json
"require" : {
        [...]
        "yosimitso/workingforumbundle" : "dev-master",
        "knplabs/knp-paginator-bundle": "2.4.*@dev",
        "knplabs/knp-markdown-bundle": "~1.3"
    }
```

Enregistrez le bundle dans votre AppKernel
Ajoutez à votre app/config.yml

````yml
yosimitso_forum:
    topic_per_page: 10
    post_per_page: 5
    date_format: 'd/m/Y H:i:s'
```    
Vous pouvez surcharger les fichiers de traductions

Votre entité Utilisateur à besoin de ces propriétés avec getter et setter
````php
       /**
     * @var integer
     * @ORM\Column(name="nb_post", type="integer")
     */
	 protected $nbPost;
 /**   
         * @var string
         * @ORM\Column(name="avatar_url", type="string",nullable=true)
         */
   
        protected $avatarUrl;
 /**   
         * @var string
         * @ORM\Column(name="username", type="string",nullable=true)
         */
   
        protected $username;
```

Todo
-----------
- Suppression d'un topic par un modérateur

