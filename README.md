# synthi-tts-revamped

An improved version of the [synthi speech synthesizer originally created at Jugend hackt Berlin 2019](https://github.com/Jugendhackt/synthi-tts), using the same technologies but with more intelligent processing of the available data.
 
This code is released under the [MIT License.](LICENSE) The original code was mainly written by [@schokotets](https://github.com/schokotets) and [@JackOBIsReal](https://github.com/JackOBIsReal) - thank you guys a lot! Although almost none of it was retained in this repository, I drew a lot of inspiration from their work.

## TODO

Although we can create understandable speech at the moment, some more things will have to be done to make it sound even more natural:

* Save the same context (and phoneme) multiple times, but include context as to where it has been used (surrounding words, etc.)
* do not ignore emphasis
* Ignore unusable slices (silence, too short, etc.)
* Remove glottal stops automatically
* implement interface so the system/a browser can use the voices

## Dependencies

Synthi depends on several other projects, namely

* composer: fetch other php dependencies
* docker: required to run the container of the forced aligner gentle
* gentle: aligning audio with transcript on phoneme level (is automatically downloaded and installed when the model creation script creates the docker container)
* espeak: handles correct pronunciation and emphasis
* ffmpeg: slices speech

## Manual work

**It looks more difficult than it is!**

To use synthi, grab the source code and run ```composer install``` to fetch all external php dependencies as well as creating an autoloader.

Although voice models are generated automatically, you will have to provide an audio file and a transcript of what was said. The longer your audio, the better the quality of the resulting voice model.

In order to create the model, run 

```./create-voicemodel.sh </full/path/to/voice/files> <name_of_audio_inside_voice_file_path> <name_of_transcript_in_path> <name_of_model>```

If you'd like to use my included example, it would look something like this: 

```sudo ./create-voicemodel.sh /home/niklas/Documents/GitProjects/synthi-tts/models/niklas/base niklas-baseaudio.mp3 niklas-transcript.txt niklas```

That's it! Nothing more for you to do ;)

## Usage

To generate speech, use synthesizer.php like so:

```php synthesizer.php niklas Hello World```
