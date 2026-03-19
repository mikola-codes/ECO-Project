    
#include <windows.h>
#include <iostream>
#include <fstream>
#include <string>
#include <vector>
#include <sstream>
#include <iomanip>

// --- Constants ---
constexpr int MAX_STRING_LENGTH = 128;
constexpr int MAX_DEVICE_NAME_LENGTH = 1024;
constexpr int DPFPDD_SUCCESS = 0;
constexpr int DPFPDD_PRIORITY_EXCLUSIVE = 4;
constexpr int DPFPDD_IMAGE_FORMAT_PIXELS = 0;
constexpr int DPFPDD_IMAGE_PROCESS_DEFAULT = 0;
constexpr unsigned int DPFJ_FORMAT_ANSI_378 = 0x001B0001;
constexpr int DPFJ_SUCCESS = 0;
constexpr int MAX_FEATURE_DATA_SIZE = (26 + 4 + 255 * 6 + 2);
constexpr int IMAGE_BUFFER_SIZE = 500000;
constexpr unsigned int MATCH_THRESHOLD = 21474; // Default threshold for false positive rate of 0.001%
constexpr unsigned int CAPTURE_TIMEOUT_MS = 30000;

// --- SDK Structures ---
typedef void* DPFPDD_DEV;
typedef struct { int major, minor, maintenance; } ScannerVersionInfo;
typedef struct { char vendor_name[MAX_STRING_LENGTH]; char product_name[MAX_STRING_LENGTH]; char serial_number[MAX_STRING_LENGTH]; } ScannerHardwareDescription;
typedef struct { unsigned short vendor_id, product_id; } ScannerHardwareId;
typedef struct { ScannerVersionInfo hardware_version, firmware_version; unsigned short bcd_revision; } ScannerHardwareVersion;
typedef struct { unsigned int size; char name[MAX_DEVICE_NAME_LENGTH]; ScannerHardwareDescription description; ScannerHardwareId id; ScannerHardwareVersion version; unsigned int modality, technology; } ScannerDeviceInfo;
typedef struct { unsigned int size; int can_capture_image, can_stream_image, can_extract_features, can_match, can_identify, has_fingerprint_storage; unsigned int indicator_type; int has_power_management, has_calibration, piv_compliant; unsigned int resolution_count; unsigned int resolutions[1]; } ScannerDeviceCapabilities;
typedef struct { unsigned int size, image_format, image_processing, image_resolution; } ScannerCaptureParameters;
typedef struct { unsigned int size, width, height, resolution, bits_per_pixel; } ScannerImageInfo;
typedef struct { unsigned int size; int success; unsigned int quality, score; ScannerImageInfo image_info; } ScannerCaptureResult;

// --- Function Pointers for SDK DLLs ---
typedef int (__stdcall *InitLibraryFunc)(void);
typedef int (__stdcall *ExitLibraryFunc)(void);
typedef int (__stdcall *QueryDevicesFunc)(unsigned int*, ScannerDeviceInfo*);
typedef int (__stdcall *OpenReaderFunc)(char*, unsigned int, DPFPDD_DEV*);
typedef int (__stdcall *CloseReaderFunc)(DPFPDD_DEV);
typedef int (__stdcall *GetCapabilitiesFunc)(DPFPDD_DEV, ScannerDeviceCapabilities*);
typedef int (__stdcall *CaptureFunc)(DPFPDD_DEV, ScannerCaptureParameters*, unsigned int, ScannerCaptureResult*, unsigned int*, unsigned char*);
typedef int (__stdcall *ExtractFeaturesFunc)(const unsigned char*, unsigned int, unsigned int, unsigned int, unsigned int, int, unsigned int, int, unsigned char*, unsigned int*);
typedef int (__stdcall *CompareFeaturesFunc)(unsigned int, unsigned char*, unsigned int, unsigned int, unsigned int, unsigned char*, unsigned int, unsigned int, unsigned int*);

// --- Global SDK Context ---
struct SdkContext {
    HMODULE captureDll = nullptr;
    HMODULE featureDll = nullptr;
    InitLibraryFunc dpfpdd_init = nullptr;
    ExitLibraryFunc dpfpdd_exit = nullptr;
    QueryDevicesFunc dpfpdd_query_devices = nullptr;
    OpenReaderFunc dpfpdd_open_ext = nullptr;
    CloseReaderFunc dpfpdd_close = nullptr;
    GetCapabilitiesFunc dpfpdd_get_device_capabilities = nullptr;
    CaptureFunc dpfpdd_capture = nullptr;
    ExtractFeaturesFunc dpfj_create_fmd_from_raw = nullptr;
    CompareFeaturesFunc dpfj_compare = nullptr;

    ~SdkContext() {
        if (dpfpdd_exit) dpfpdd_exit();
        if (captureDll) FreeLibrary(captureDll);
        if (featureDll) FreeLibrary(featureDll);
    }
} sdk;

// --- Helper Functions ---

// Convert byte array to hex string
std::string BytesToHex(const unsigned char* data, unsigned int size) {
    std::ostringstream oss;
    oss << std::hex << std::setfill('0');
    for (unsigned int i = 0; i < size; ++i) {
        oss << std::setw(2) << static_cast<int>(data[i]);
    }
    return oss.str();
}

// Convert hex string to byte array
std::vector<unsigned char> HexToBytes(const std::string& hex) {
    std::vector<unsigned char> bytes;
    for (size_t i = 0; i < hex.length(); i += 2) {
        std::string byteString = hex.substr(i, 2);
        unsigned char byte = static_cast<unsigned char>(strtol(byteString.c_str(), nullptr, 16));
        bytes.push_back(byte);
    }
    return bytes;
}

// Print error and return false
bool LogError(const std::string& message) {
    std::cout << "ERROR:" << message << std::endl;
    return false;
}

// --- SDK Operations ---

bool InitializeSdk() {
    sdk.captureDll = LoadLibraryA("dpfpdd.dll");
    sdk.featureDll = LoadLibraryA("dpfj.dll");
    if (!sdk.captureDll) sdk.captureDll = LoadLibraryA("C:\\Program Files\\DigitalPersona\\U.are.U SDK\\Windows\\Lib\\x64\\dpfpdd.dll");
    if (!sdk.featureDll) sdk.featureDll = LoadLibraryA("C:\\Program Files\\DigitalPersona\\U.are.U SDK\\Windows\\Lib\\x64\\dpfj.dll");
    
    if (!sdk.captureDll || !sdk.featureDll) return LogError("Cannot load SDK DLLs");

    sdk.dpfpdd_init = (InitLibraryFunc)GetProcAddress(sdk.captureDll, "dpfpdd_init");
    sdk.dpfpdd_exit = (ExitLibraryFunc)GetProcAddress(sdk.captureDll, "dpfpdd_exit");
    sdk.dpfpdd_query_devices = (QueryDevicesFunc)GetProcAddress(sdk.captureDll, "dpfpdd_query_devices");
    sdk.dpfpdd_open_ext = (OpenReaderFunc)GetProcAddress(sdk.captureDll, "dpfpdd_open_ext");
    sdk.dpfpdd_close = (CloseReaderFunc)GetProcAddress(sdk.captureDll, "dpfpdd_close");
    sdk.dpfpdd_get_device_capabilities = (GetCapabilitiesFunc)GetProcAddress(sdk.captureDll, "dpfpdd_get_device_capabilities");
    sdk.dpfpdd_capture = (CaptureFunc)GetProcAddress(sdk.captureDll, "dpfpdd_capture");
    sdk.dpfj_create_fmd_from_raw = (ExtractFeaturesFunc)GetProcAddress(sdk.featureDll, "dpfj_create_fmd_from_raw");
    sdk.dpfj_compare = (CompareFeaturesFunc)GetProcAddress(sdk.featureDll, "dpfj_compare");

    if (!sdk.dpfpdd_init || !sdk.dpfpdd_exit || !sdk.dpfpdd_query_devices || !sdk.dpfpdd_open_ext || !sdk.dpfpdd_close || !sdk.dpfpdd_capture || !sdk.dpfj_create_fmd_from_raw || !sdk.dpfj_compare) {
        return LogError("Cannot resolve SDK functions");
    }

    if (sdk.dpfpdd_init() != DPFPDD_SUCCESS) return LogError("SDK init failed");
    return true;
}

// Scans a finger and returns the extracted feature data (FMD)
bool ScanFingerprint(std::vector<unsigned char>& outFeatureData) {
    unsigned int deviceCount = 0;
    sdk.dpfpdd_query_devices(&deviceCount, nullptr);
    if (deviceCount == 0) return LogError("No scanner found");

    std::vector<ScannerDeviceInfo> devices(deviceCount);
    devices[0].size = sizeof(ScannerDeviceInfo);
    sdk.dpfpdd_query_devices(&deviceCount, devices.data());

    DPFPDD_DEV readerHandle = nullptr;
    if (sdk.dpfpdd_open_ext(devices[0].name, DPFPDD_PRIORITY_EXCLUSIVE, &readerHandle) != DPFPDD_SUCCESS) {
        return LogError("Cannot open scanner");
    }

    // Determine scanner resolution (DPI)
    unsigned int dpi = 500;
    ScannerDeviceCapabilities capabilities = {0};
    capabilities.size = sizeof(capabilities);
    if (sdk.dpfpdd_get_device_capabilities(readerHandle, &capabilities) == DPFPDD_SUCCESS && capabilities.resolution_count > 0) {
        dpi = capabilities.resolutions[0];
    }

    // Prepare capture
    ScannerCaptureParameters captureSettings = {0};
    captureSettings.size = sizeof(captureSettings);
    captureSettings.image_format = DPFPDD_IMAGE_FORMAT_PIXELS;
    captureSettings.image_processing = DPFPDD_IMAGE_PROCESS_DEFAULT;
    captureSettings.image_resolution = dpi;

    ScannerCaptureResult captureResult = {0};
    captureResult.size = sizeof(captureResult);
    captureResult.image_info.size = sizeof(captureResult.image_info);

    std::vector<unsigned char> imageBuffer(IMAGE_BUFFER_SIZE);
    unsigned int actualImageSize = IMAGE_BUFFER_SIZE;

    // Capture the image
    int captureStatus = sdk.dpfpdd_capture(readerHandle, &captureSettings, CAPTURE_TIMEOUT_MS, &captureResult, &actualImageSize, imageBuffer.data());
    
    if (captureStatus != DPFPDD_SUCCESS || !captureResult.success) {
        sdk.dpfpdd_close(readerHandle);
        return LogError("Capture failed");
    }

    // Extract features (FMD) from the raw image
    outFeatureData.resize(MAX_FEATURE_DATA_SIZE);
    unsigned int featureDataSize = MAX_FEATURE_DATA_SIZE;
    
    int extractStatus = sdk.dpfj_create_fmd_from_raw(
        imageBuffer.data(), actualImageSize,
        captureResult.image_info.width, captureResult.image_info.height, captureResult.image_info.resolution,
        0, 0, DPFJ_FORMAT_ANSI_378,
        outFeatureData.data(), &featureDataSize
    );

    sdk.dpfpdd_close(readerHandle);

    if (extractStatus != DPFJ_SUCCESS) return LogError("Feature extraction failed");
    
    outFeatureData.resize(featureDataSize); // Trim to actual size
    return true;
}

// Compares the scanned fingerprint against a database file
void RunVerification(const std::vector<unsigned char>& scannedFmd, const std::string& dataFilePath) {
    std::ifstream dataFile(dataFilePath);
    if (!dataFile.is_open()) {
        LogError("Cannot open data file " + dataFilePath);
        return;
    }

    int matchedEmployeeId = -1;
    unsigned int bestScore = static_cast<unsigned int>(-1);
    std::string line;

    // File format: "employee_id|fmd_hex_string"
    while (std::getline(dataFile, line)) {
        if (line.empty() || line[0] == '\r') continue;

        size_t separatorPos = line.find('|');
        if (separatorPos == std::string::npos) continue;

        int employeeId = std::stoi(line.substr(0, separatorPos));
        std::string storedHex = line.substr(separatorPos + 1);
        
        // Remove trailing \r from string if it exists from Windows CRLF lines
        if (!storedHex.empty() && storedHex.back() == '\r') {
             storedHex.pop_back();
        }

        std::vector<unsigned char> storedFmd = HexToBytes(storedHex);

        unsigned int dissimilarityScore = 0;
        int compareResult = sdk.dpfj_compare(
            DPFJ_FORMAT_ANSI_378, const_cast<unsigned char*>(scannedFmd.data()), scannedFmd.size(), 0,
            DPFJ_FORMAT_ANSI_378, storedFmd.data(), storedFmd.size(), 0,
            &dissimilarityScore
        );

        if (compareResult == DPFJ_SUCCESS && dissimilarityScore < MATCH_THRESHOLD) {
            if (dissimilarityScore < bestScore) {
                bestScore = dissimilarityScore;
                matchedEmployeeId = employeeId;
            }
        }
    }

    if (matchedEmployeeId >= 0) {
        std::cout << "MATCH:" << matchedEmployeeId;
    } else {
        std::cout << "NOMATCH";
    }
}

// --- Main Entry Point ---

int main(int argc, char* argv[]) {
    if (argc < 2) {
        std::cout << "ERROR:Usage: scanner.exe enroll OR scanner.exe verify data.tmp\n";
        return 1;
    }

    std::string mode = argv[1];

    if (!InitializeSdk()) return 1;

    std::vector<unsigned char> scannedFeatures;
    if (!ScanFingerprint(scannedFeatures)) return 1;

    if (mode == "enroll") {
        std::cout << BytesToHex(scannedFeatures.data(), scannedFeatures.size());
    } 
    else if (mode == "verify") {
        if (argc < 3) {
            std::cout << "ERROR:Verify mode requires a data file path\n";
            return 1;
        }
        RunVerification(scannedFeatures, argv[2]);
    } 
    else {
        std::cout << "ERROR:Unknown mode '" << mode << "'. Use 'enroll' or 'verify'\n";
        return 1;
    }

    return 0;
}
