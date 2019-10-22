# sinatoki

I aim to create almost natural-sounding and understandable synthetic voices based on the principle of sound concatenation from an automatically pre-built speaker database. It should be as easy as possible for a user to make new models and use them immediately, without major technical overhead.

[An example](example.mp3) of generated speech is included in this repository, as well as basic files to generate a model based on **my** voice. 

## Dependencies and installation

Sinatoki depends on several other **open source** projects, namely

* [composer](https://getcomposer.org/): fetch other php dependencies
* [docker](https://www.docker.com/get-started): required to run the container of the forced aligner gentle
* [gentle](https://github.com/lowerquality/gentle): aligning audio with transcript on phoneme level (is automatically downloaded and installed when the model creation script creates the docker container)
* [espeak](http://espeak.sourceforge.net/): handles correct pronunciation and emphasis
* [ffmpeg](https://ffmpeg.org/): slices speech
* mp3 codecs provided by your distribution

Make sure you have installed them on your system before proceeding.

```bash
# Ubuntu only, getting the dependencies
sudo apt install php php-cli composer docker.io espeak ffmpeg git

# Cross-platform installation
git clone https://github.com/nkreer/sinatoki
cd sinatoki
composer install

# Creating a demo model from included files
# You have to start the docker daemon first
sudo dockerd
# Then run the included script 
# This requires root privileges because it interacts with the container
# Note that the tool only supports .mp3 as base file format for now
sudo ./create-voicemodel.sh models/niklas/base niklas-baseaudio.mp3 niklas-transcript.txt niklas

# Generate example speech (vlc required)
php synthesizer.php niklas Hello World
vlc output.mp3
```

If you'd like to hack & contribute to the project, you'll be happy to find well-commented and descriptive source code. I look forward to see your pull requests!

## Considerations

Please note that generating voice models will take a significant amount of time right now, depending on your hardware and the length of your prepared recording.

Ethically, using this project shouldn't be much of a concern as you can clearly hear that sinatoki voices sound very robotic and not natural. The primary use of this tool is in entertainment and education - not so much in actual real-world scenarios.

## Todo

Although we can create understandable speech at the moment, some more things will have to be done to make it sound even more natural:

* Save the same context (and phoneme) multiple times, but include context as to where it has been used (surrounding words, etc.)
* do not ignore emphasis
* Ignore unusable slices (silence, too short, etc.)
* Remove glottal stops automatically
* implement interface so the system/a browser can use the voices

Feel free to work on either of these and contribute your changes back to the project!

## Attribution

"sina toki" is Toki Pona for "your voice", expressing all the aims of this project in a simple phrase. Ona pona?

The original code was written by [@schokotets](https://github.com/schokotets) and [@JackOBIsReal](https://github.com/JackOBIsReal) - thank you guys a lot! Although almost none of it was retained in this repository, I drew a lot of inspiration from their work.

Sinatoki is an improved version of the [speech synthesizer originally created at Jugend hackt Berlin 2019](https://github.com/Jugendhackt/synthi-tts), using the same technologies but with more intelligent processing of the available data.
