<?php
/**
 * Created by PhpStorm.
 * User: doncredas
 * Date: 15/04/15
 * Time: 1.42
 */

namespace Blog\Controller;


use Blog\Domain\Comment;
use Blog\Form\Type\CommentType;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


class HomeController
{

    /**
     * Home page controller
     *
     * @param Application $app The silex Application
     * @return mixed
     */
    public function indexAction(Application $app) {
        $articles = $app['Dao.article']->findAll();
        return $app['twig']->render('index.html.twig', array('articles' => $articles));
    }


    /**
     * @param integer $id Article id
     * @param Application $app
     * @param Request $request
     * @return mixed
     */
    public function viewArticleAction($id, Application $app, Request $request) {
        $article = $app['Dao.article']->findArticle($id);

        $user = $app['security']->getToken()->getUser();
        $commentFormView = null;
        if ($app['security']->isGranted('IS_AUTHENTICATED_FULLY')) {
            // A user is fully authenticated : he can add comments
            $comment = new Comment();
            $comment->setArticle($article);
            $comment->setAuthor($user);
            $commentForm = $app['form.factory']->create(new CommentType(), $comment);
            $commentForm->handleRequest($request);
            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $app['Dao.comment']->save($comment);
                $app['session']->getFlashBag()->add('success', 'Your comment was succesfully added.');
            }
            $commentFormView = $commentForm->createView();
        }
        $comments = $app['Dao.comment']->findAllByArticle($id);
        return $app['twig']->render('article.html.twig', array(
            'article' => $article,
            'comments' => $comments,
            'commentForm' => $commentFormView
        ));
    }

    /**
     * User login controller
     *
     * @param Application $app
     * @param Request $request
     * @return mixed
     */
    public function loginAction(Application $app, Request $request){
        return $app['twig']->render('login.html.twig', array(
            'error'         => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
        ));
    }

    /**
     * About page controller
     *
     * @param Application $app
     * @return mixed
     */
    public function aboutAction(Application $app) {
        return $app['twig']->render('about.html.twig');
    }

    /**
     * Profile controller
     *
     * @param Application $app
     * @return mixed
     */
    public function profileAction(Application $app) {
        return $app['twig']->render('error.html.twig', array('message' => 'Under construction...', 'code' => '007'));
    }

}