dtw-php
=======

A worker that uses Dynamic Time Warping to classify symbols.

Classification performance
--------------------------
2014-06-05

With a dataset of the following symbols (occurences in bracets):
A (192), B (72), C (98), D (66), E (85), F (74), G (73), H (70), I (17), J (16), K (16), L (28), M (68), N (12), O (36), P (34), Q (12), R (13), S (11), T (16), U (13), V (12), W (18), X (14), Y (13), Z (23), \rightarrow (67), 0 (81), 1 (28), 2 (26), 3 (27), 4 (71), 5 (23), 6 (18), 7 (23), 8 (21), 9 (20), \pi (77), \alpha (76), \beta (69), \sum (21), \sigma (14), a (15), b (14), c (19), d (11), e (11), f (12), g (11), h (12), j (10), k (11), l (12), m (11), n (16), o (11), p (11), q (24), r (11), s (12), t (16), u (15), v (11), w (10), x (16), y (13), z (12), \Sigma (70), \gamma (50), \Gamma (63), \delta (16), \Delta (67), \zeta (14), \eta (15), \theta (12), \Theta (12), \epsilon (14), \varepsilon (65), \iota (11), \kappa (14), \varkappa (11), \lambda (29), \Lambda (11), \mu (15), \nu (12), \xi (13), \Pi (21), \rho (11), \varrho (10), \tau (29), \phi (11), \Phi (12), \varphi (12), \chi (12), \psi (11), \Psi (12), \omega (11), \Omega (57), \partial (30), \int (10), \cdot (12), \leq (19), \geq (14), < (42), > (30), \subset (13), \supset (50), \subseteq (13), \supseteq (13), \cong (14), \propto (21), - (17), + (17), \mathbb{R} (10), \copyright (10), \checkmark (10), \nabla (11), \heartsuit (18), \frownie (13), \Frowny (10), \textasciitilde (27), \mathbb{Q} (23)

and a classification time of about 4.2 seconds / symbol this classifier got a
classification accuracy of

* Top-1-Classification (10-fold cross-validated): 0.8318930300639019
* Top-10-Classification (10-fold cross-validated): 0.9692571810953939