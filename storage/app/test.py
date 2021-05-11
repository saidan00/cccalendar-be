#!/usr/bin/env python
import json, sys, os, math, warnings
import string
import collections

from nltk import word_tokenize
from nltk.stem import PorterStemmer
from nltk.corpus import stopwords
from sklearn.cluster import KMeans
from sklearn.feature_extraction.text import TfidfVectorizer
from pprint import pprint

script_dir = os.path.dirname(__file__) #<-- absolute dir the script is in

with open(script_dir + '/event_1.json') as json_file:
    jsonString = json_file.read()
    diaries = json.loads(jsonString)

def process_text(text, stem=True):
    """ Tokenize text and stem words removing punctuation """
    # text = text.translate(None, string.punctuation)
    text = text.translate(str.maketrans('','',string.punctuation))
    tokens = word_tokenize(text)

    if stem:
        stemmer = PorterStemmer()
        tokens = [stemmer.stem(t) for t in tokens]

    return tokens


def cluster_texts(texts, clusters=3):
    """ Transform texts to Tf-Idf coordinates and cluster texts using K-Means """
    myStopwords = stopwords.words('english')
    # stopwords_vn = stopwords.words('vietnamese')
    stopwords_vn =['õ']
    myStopwords.extend(stopwords_vn)
    vectorizer = TfidfVectorizer(tokenizer=process_text,
                                 stop_words=myStopwords,
                                 max_df=0.5,
                                 min_df=0.1,
                                 lowercase=True)

    tfidf_model = vectorizer.fit_transform(texts)
    km_model = KMeans(n_clusters=clusters)
    km_model.fit(tfidf_model)

    clustering = collections.defaultdict(list)

    for idx, label in enumerate(km_model.labels_):
        clustering[label].append(idx)

    return clustering


if __name__ == "__main__":
    with warnings.catch_warnings():
        warnings.simplefilter("ignore")
        clusters = cluster_texts(diaries, 10)
        dictClusters = dict(clusters)
        jsonClustersString = {str(k):v for k,v in dictClusters.items()}
        jsonClusters = json.dumps(jsonClustersString)
        # pprint(dict(clusters))
        print(jsonClusters)
