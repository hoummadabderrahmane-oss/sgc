    <?php if (isLoggedIn()): ?>
                </main>
            </div>
        </div>
    <?php else: ?>
        </div>
    <?php endif; ?>
    
    <?php
    // Detect base path for JS
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = dirname(dirname($scriptName));
    $basePath = str_replace('\\', '/', $basePath);
    if ($basePath == '/' || $basePath == '\\') $basePath = '';
    $assetsPath = $basePath . '/assets';
    ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $assetsPath ?>/js/main.js"></script>
</body>
</html>