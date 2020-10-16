# MTM-Redis

## What is # MTM-Redis-Api?

Access Redis using PHP, async pub/sub

### Get a connection:

```
$protocol		= "tcp";
$host			= "127.0.0.1";
$port			= 6379;
$auth			= null; //or the authentication string
$connTimeout	= 30;
$clientObj		= \MTM\RedisApi\Facts::getClients()->getV1($protocol, $host, $port, $auth, $connTimeout);
```

### Get a database

```
$dbId	= 3;
$dbObj	= $clientObj->getDatabase($dbId);
```

#### Set a key value

```
$key		= "myKey";
$value		= "myStringValue";
$dbObj->setValueByKey($key, $value);

```

#### Get a key value

```
$key		= "myKey";
$throw		= true; //optional, default false. If set true method will throw if key does not exist.
$value		= $dbObj->getValueByKey($key, $throw);

```


### Get a Channel

Note: getting a channel does not subscribe it. You can publish messages to a channel that is not subscribed

#### Regular channel

```
$chanName		= "myChannel";
$chanObj		= $clientObj->addChannel($chanName, false);

```

### Publish a message

```
$ignoreDub		= false; //optional, defaults to false. Allows much faster sending when set to true, but channel gets a copy of its own message
$msgStr		= "myMessage"; //serialize if not a string
$subCount		= $chanObj->setMessage($msgStr, $ignoreDub); //returns how many subs got the message
```


#### Pattern channel

```
$pattern		= "*";
$chanObj		= $clientObj->addChannel($pattern, true);

```
Note: pattern channels cannot publish messages



### Subscribe to a Channel

```
$chanObj->subscribe();

```

### Unsubscribe to a Channel

```
$chanObj->unsubscribe();

```

### Get many messages from a channel

```
$maxMsg	= 5; //-1 to get all
$timeout	= 1000; //ms to wait for atleast one message
$msgArr	= $chanObj->getMessages($maxMsg, $timeout); //array of message objs
```

### Get one messages from a channel

```
$timeout	= 1000; //ms to wait for the message
$stdObj	= $chanObj->getMessage($timeout); //single message obj
```

### Quit the client

```
$clientObj->quit();
```





