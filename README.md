# About

This script allows you to steal Philips Hue light bulbs from one bridge
to another. Particularly useful when buying packs that come with
lightbulbs that are factory paired (starter kits) or used bulbs on craigslist / ebay.

# Usage

the flow is pretty simple:

1. get bridge ip
2. get bridge api token
3. use token to initiate a touchlink operation

## get bridge IP


If you know the IP of your bridge already, you can skip this step.  Otherwise, I'll try to get the IP of your bridge from [Philips](https://www.developers.meethue.com/documentation/getting-started).  This will only work if your bridge has been able to successfully connect (all three LEDs are on) and you're using the same internet connection that your bridge is using to reach the mother ship.

You can use the `bridges` command to get a list of bridges using your internet connection to phone home.

```
$ php steal.php bridges

Bridge [0] is at IP: [192.168.99.99]
```

## register a client on the bridge


Once you have an IP, you'll need to get an API token.  Do this with the `client` command.  You must supply the IP address of your bridge.

The Hue bridge will only issue API tokens during a very brief window of time (about 20 seconds). You must press the 'link' button before this window closes.  You are prompted to push the link button before `steal.php` will ask for an API token.  Please push the link button on your bridge and then quickly press enter to indicate to `steal.php` that it is OK to request a client token.


E.G.:

```
# get a client token
$ php steal.php client 192.168.99.9

press the link button on your bridge and then hit enter. Bridge: http://192.168.99.9/api


You now have client [fRVhWyaw3wlD7uYStt0mfRVhWyaw3wlD7uYStt0m] on bridge [192.168.99.9]

```


## steal bulbs

Now that you have a credential to use with the Hue bridge API, you can steal bulbs and associate them w/ your bridge.

You use the `steal` command to do this.  For the `steal` command to work, you will need to move the light bulb to no more than 6 inches or so from the bridge.  To do this, i usually use a table lamp, remove the lamp shade and lay the lamp down on the ground next to the bridge.


When stealing, the bulb closest to the bridge will blink a wold white before reverting to it's prior color.  The blinking indicates that the bulb has agreed to communicate with your bridge

E.G.:

```
# use the client token to begin a touchlink operation
php steal.php steal 192.168.99.9 fRVhWyaw3wlD7uYStt0mfRVhWyaw3wlD7uYStt0m

Make sure the light bulb is on and no more than 6 inches from the bridge and then press enter


Wait for light to blink. Repeat steal process for each light and then search for new lights in the app.

```




## Full console out

```
# get (crude) help
$ php steal.php

You need to specify a command and either an IP or an IP and a client token.  See readme.md

# get a list of bridges
$ php steal.php bridges

Bridge [0] is at IP: [192.168.99.9]

# get a client token
$ php steal.php client 192.168.99.9

press the link button on your bridge and then hit enter. Bridge: http://192.168.99.9/api


You now have client [fRVhWyaw3wlD7uYStt0mfRVhWyaw3wlD7uYStt0m] on bridge [192.168.99.9]
$ php steal.php steal 192.168.99.9 fRVhWyaw3wlD7uYStt0mfRVhWyaw3wlD7uYStt0m

Make sure the light bulb is on and no more than 6 inches from the bridge and then press enter


Wait for light to blink. Repeat steal process for each light and then search for new lights in the app.
```


# ToDo:

(pr's welcome; i hacked this together in an evening for personal use... it does what i need, so i'm not going to add to it until it breaks)

0. get friendly name of bridge from API
1. save client token to disk / check
2. automate client registration once bridge has been specified
3. if only one bridge, give user option to manually enter or automatically get client
4. kick off new light search (instead of sending user to app)
5. code could be re-factored a bit
6. implement literally _any_ sanity checks / do recovery
