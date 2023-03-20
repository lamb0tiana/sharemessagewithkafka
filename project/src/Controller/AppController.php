<?php
// src/Controller/BlogController.php
namespace App\Controller;

use App\Message\Notification;
use Enqueue\MessengerAdapter\EnvelopeItem\TransportConfiguration;
use Enqueue\RdKafka\RdKafkaConnectionFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    #[Route('/sendMessage')]
    public function sendMessage(Request $request,MessageBusInterface $bus){
      $message = $request->query->get('m', 'hello');
        $x =  $bus->dispatch(new Notification($message))->with(new TransportConfiguration([
            'topic' => 'quickstart',
            'metadata' => [
                'key' => 'foo.bar',
                'partition' => 0,
                'timestamp' => (new \DateTimeImmutable())->getTimestamp(),
                'messageId' => uniqid('kafka_', true),
            ]
        ]));
        return $this->json(['topic' => 'quickstart', 'message' => $message]);
    }

    #[Route('/produce', name: 'blog_list')]
    public function produce(Request $request): Response
    {
        $connectionFactory = new RdKafkaConnectionFactory([
            'global' => [
                'group.id' => 'mygroup',
                'metadata.broker.list' => 'broker:9092',
                'enable.auto.commit' => 'false',
            ],
            'topic' => [
                'auto.offset.reset' => 'beginning',
            ],
        ]);
        $msg = ($request->query->get('m', 'ok'));
        $context = $connectionFactory->createContext();
        $message = $context->createMessage($msg);
        $fooTopic = $context->createTopic('quickstart');
        $xx = $context->createProducer()->send($fooTopic, $message);
        echo 'A test message has been sent to the topic "quickstack".' . PHP_EOL;


        $fooQueue = $context->createQueue('foo');

      $ppp =   $context->createProducer()->send($fooQueue, $message);
        dd($xx, $ppp);
    }

    #[Route('/consume', name: 'consume')]
    public function consume(){
            $connectionFactory = new RdKafkaConnectionFactory([
                'global' => [
                    'group.id' => 'mygroup',
                    'metadata.broker.list' => 'broker:9092',
                    'enable.auto.commit' => 'false',
                ],
                'topic' => [
                    'auto.offset.reset' => 'beginning',
                ],
            ]);

            $context = $connectionFactory->createContext();
            $fooQueue = $context->createQueue('foo');
            $consumer = $context->createConsumer($fooQueue);
//        $consumer->setCommitAsync(true);
            $message = $consumer->receive();
            $aa = $consumer->acknowledge($message);
            dump($aa, $message->getKafkaMessage());
        die;
    }
}