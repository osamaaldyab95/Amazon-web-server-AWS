import time
import board
import adafruit_dht
import RPi.GPIO as GPIO
import netifaces as ni
import urllib.error
from urllib.request import urlopen
  
def measure():
    # while running high white led
    GPIO.output(white, 1)
    # Initial sensor
    GPIO.setmode(GPIO.BCM)
    
    while True:
        try:
            #set status LEDs low again
            GPIO.output(red, 0)
            GPIO.output(green, 0)

            # Read Sensor and send data via GET to Amazon-Server
            temperature = dhtDevice.temperature
            humidity = dhtDevice.humidity
            print("Temp: %f C    Humidity: %f " % (temperature, humidity))

            send_url = "http://52.59.187.231/save.php?temperature=%f&humidity=%f" % (temperature, humidity)
            print(send_url)
            result = urlopen(send_url)

            GPIO.output(green, 1) # green

        except RuntimeError as error:
            GPIO.output(red, 1) # red
            print(error.args[0])        
        except urllib.error.URLError as error:
            GPIO.output(red, 1)
            print(error.args[0])
        except KeyboardInterrupt:
            GPIO.cleanup()
            exit()
        except:
            GPIO.output(red, 1) # red
        finally:
            #wait 60 seconds and then do again
            time.sleep(60)
            
#################################

def getIP():
    try:
        #get current local IP and send it to aws
        ip = ni.ifaddresses('wlan0')[ni.AF_INET][0]['addr']
        send_url = "http://52.59.187.231/save.php?ip=%s" % (ip)
        urlResult = urlopen(send_url)
        defResult = True
    except:
        defResult = False
    finally:
        return defResult

try:
    #Sensor-Ids
    green = 27
    white = 17
    red = 22

    # Initial LEDs
    GPIO.setmode(GPIO.BCM)
    GPIO.setup(green, GPIO.OUT)
    GPIO.setup(red, GPIO.OUT)
    GPIO.setup(white, GPIO.OUT)
    dhtDevice = adafruit_dht.DHT11(board.D4) #DHT-Output connected on Pin4
    
    defResult = False
    while (defResult == False):
        GPIO.output(red, 0)
        GPIO.output(white, 0)
        GPIO.output(green, 0)
        time.sleep(1)
        
        defResult = getIP()
        if defResult:
            measure()
            break
            
        GPIO.output(red, 1)
        GPIO.output(white, 1)
        GPIO.output(green, 1)
        time.sleep(4)
        
except KeyboardInterrupt:
    GPIO.cleanup()
    exit()