# synthi-tts-revamped

An improved version of the [synthi speech synthesizer originally created at Jugend hackt Berlin 2019](https://github.com/Jugendhackt/synthi-tts), using the same technologies but with more intelligent processing of the available data.
 
This code is released under the [MIT License.](LICENSE) The original code was mainly written by @schokotets and @JackOBIsReal - thank you guys a lot! Although almost none of it was retained in this repository, I drew a lot of inspiration from their work.

## Dependencies

Synthi depends on several other projects, namely

* docker: required to run the container of the forced aligner gentle
* gentle: aligining audio with transcript on phoneme level
* espeak: handles correct pronunciation
* ffmpeg: slices speech

## Manual work

Although voice models are generated completely automatically, you will have to provide an audio file and a transcript of what was being said. Please note that the longer the audio, the better the quality of the resulting voice model.

In order to create the model, run 

```./create-voicemodel.sh </full/path/to/voice/files> <name_of_audio_inside_voice_file_path> <name_of_transcript_in_path> <name_of_model>```

If you'd like to use my included example, it would look something like this: 

```sudo ./gentle-process.sh /home/niklas/Documents/GitProjects/synthi-tts/models/niklas/base niklas-baseaudio.mp3 niklas-transcript.txt niklas```

That's it! Nothing more for you to do ;)
